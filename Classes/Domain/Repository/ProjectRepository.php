<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with TYPO3 source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Causal\Sphinx\Domain\Repository;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * (Custom) Project repository.
 *
 * @category    Domain\Repository
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class ProjectRepository implements \TYPO3\CMS\Core\SingletonInterface
{

    const PROJECTS_FILENAME = 'typo3conf/sphinx-projects.json';

    /**
     * Returns all projects.
     *
     * @return \Causal\Sphinx\Domain\Model\Project[]
     */
    public function findAll()
    {
        $projects = array();
        $data = $this->loadProjects();
        foreach ($data as $p) {
            $project = $this->instantiateProjectFromArray($p);

            $key = $p['group'] . $project->getDocumentationKey();
            $projects[$key] = $project;
        }
        ksort($projects);
        return array_values($projects);
    }

    /**
     * Returns a project based on its documentation key.
     *
     * @param string $documentationKey
     * @return \Causal\Sphinx\Domain\Model\Project
     */
    public function findByDocumentationKey($documentationKey)
    {
        $projects = $this->loadProjects();
        foreach ($projects as $project) {
            if ($project['key'] === $documentationKey) {
                return $this->instantiateProjectFromArray($project);
            }
        }

        return null;
    }

    /**
     * Adds a given project.
     *
     * @param \Causal\Sphinx\Domain\Model\Project $project
     * @return bool
     */
    public function add(\Causal\Sphinx\Domain\Model\Project $project)
    {
        $projects = $this->loadProjects();
        $projects[] = array(
            'name' => $project->getName(),
            'description' => $project->getDescription(),
            'group' => $project->getGroup(),
            'key' => $project->getDocumentationKey(),
            'directory' => $project->getDirectory(),
        );

        return $this->persistProjects($projects);
    }

    /**
     * Updates a given project.
     *
     * @param \Causal\Sphinx\Domain\Model\Project $project
     * @return bool
     */
    public function update(\Causal\Sphinx\Domain\Model\Project $project)
    {
        $projects = $this->loadProjects();
        $numberOfProjects = count($projects);
        $found = false;

        for ($i = 0; $i < $numberOfProjects; $i++) {
            if ($projects[$i]['key'] === $project->getUid()) {
                $projects[$i] = array(
                    'name' => $project->getName(),
                    'language' => $project->getLanguage(),
                    'description' => $project->getDescription(),
                    'group' => $project->getGroup(),
                    'key' => $project->getDocumentationKey(),
                    'directory' => $project->getDirectory(),
                );
                $found = true;
                break;
            }
        }

        if ($found) {
            return $this->persistProjects($projects);
        }
        return false;
    }

    /**
     * Renames a project's group.
     *
     * @param string $oldName
     * @param string $newName
     * @return bool
     */
    public function renameGroup($oldName, $newName)
    {
        $projects = $this->loadProjects();
        $numberOfProjects = count($projects);

        for ($i = 0; $i < $numberOfProjects; $i++) {
            if ($projects[$i]['group'] === $oldName) {
                $projects[$i]['group'] = $newName;
            }
        }

        return $this->persistProjects($projects);
    }

    /**
     * Removes a project.
     *
     * @param string $documentationKey
     * @return bool
     */
    public function remove($documentationKey)
    {
        $projects = $this->loadProjects();
        $numberOfProjects = count($projects);
        $found = false;

        for ($i = 0; $i < $numberOfProjects; $i++) {
            if ($projects[$i]['key'] === $documentationKey) {
                unset($projects[$i]);
                $found = true;
                break;
            }
        }

        if ($found) {
            return $this->persistProjects($projects);
        }
        return false;
    }

    /**
     * Instantiate a Project domain object from raw data.
     *
     * @param array $data
     * @return \Causal\Sphinx\Domain\Model\Project
     */
    protected function instantiateProjectFromArray(array $data)
    {
        /** @var \Causal\Sphinx\Domain\Model\Project $project */
        $project = GeneralUtility::makeInstance('Causal\\Sphinx\\Domain\\Model\\Project', $data['key']);
        $project->setName($data['name']);
        // "isset" to be removed in version 1.5.0 when it's expected
        // that every project got this new key
        $project->setLanguage(isset($data['language']) ? $data['language'] : '');
        $project->setDescription($data['description']);
        $project->setGroup($data['group']);
        $project->setDirectory($data['directory']);

        return $project;
    }

    /**
     * Loads the available projects.
     *
     * @return array
     */
    protected function loadProjects()
    {
        $projects = array();
        $filename = GeneralUtility::getFileAbsFileName(static::PROJECTS_FILENAME);
        if (is_file($filename)) {
            $contents = file_get_contents($filename);
            $projects = json_decode($contents, true);
            if (!is_array($projects)) {
                $projects = array();
            }
        }
        return array_values($projects);
    }

    /**
     * Persists the projects.
     *
     * @param array $projects
     * @return bool true if the list of projects was successfully persisted
     */
    protected function persistProjects(array $projects)
    {
        // Be sure to have contiguous indices
        $projects = array_values($projects);

        $filename = GeneralUtility::getFileAbsFileName(static::PROJECTS_FILENAME);
        if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
            $content = json_encode($projects, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        } else {
            $content = json_encode($projects);
            if (count($projects) > 0) {
                // Mimic JSON_UNESCAPE_SLASHES
                $content = str_replace('\\/', '/', $content);
                // Mimic JSON_PRETTY_PRINT (for our known data structure)
                $content = "[\n\t" . substr($content, 1, -1) . "\n]";
                $content = str_replace('{"', "{\n\t\t\"", $content);
                $content = str_replace('"}', "\"\n\t}", $content);
                $content = str_replace('},', "},\n\t", $content);
                $content = str_replace('","', "\",\n\t\t\"", $content);
                $content = str_replace(array(':{', '":"'), array(': {', '": "'), $content);
            }
        }
        return GeneralUtility::writeFile($filename, $content);
    }

}
