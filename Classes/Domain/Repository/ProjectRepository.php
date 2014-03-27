<?php
namespace Causal\Sphinx\Domain\Repository;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013-2014 Xavier Perseguers <xavier@causal.ch>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

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
class ProjectRepository implements \TYPO3\CMS\Core\SingletonInterface {

	const PROJECTS_FILENAME = 'typo3conf/sphinx-projects.json';

	/**
	 * Returns all projects.
	 *
	 * @return \Causal\Sphinx\Domain\Model\Project[]
	 */
	public function findAll() {
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
	public function findByDocumentationKey($documentationKey) {
		$projects = $this->loadProjects();
		foreach ($projects as $project) {
			if ($project['key'] === $documentationKey) {
				return $this->instantiateProjectFromArray($project);
			}
		}

		return NULL;
	}

	/**
	 * Updates a given project.
	 *
	 * @param \Causal\Sphinx\Domain\Model\Project $project
	 * @return bool
	 */
	public function update(\Causal\Sphinx\Domain\Model\Project $project) {
		$projects = $this->loadProjects();
		$numberOfProjects = count($projects);
		$found = FALSE;

		for ($i = 0; $i < $numberOfProjects; $i++) {
			if ($projects[$i]['key'] === $project->getUid()) {
				$projects[$i] = array(
					'name' => $project->getName(),
					'description' => $project->getDescription(),
					'group' => $project->getGroup(),
					'key' => $project->getDocumentationKey(),
					'directory' => $project->getDirectory(),
				);
				$found = TRUE;
				break;
			}
		}

		if ($found) {
			return $this->persistProjects($projects);
		}
		return FALSE;
	}

	/**
	 * Renames a project's group.
	 *
	 * @param string $oldName
	 * @param string $newName
	 * @return bool
	 */
	public function renameGroup($oldName, $newName) {
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
	public function remove($documentationKey) {
		$projects = $this->loadProjects();
		$numberOfProjects = count($projects);
		$found = FALSE;

		for ($i = 0; $i < $numberOfProjects; $i++) {
			if ($projects[$i]['key'] === $documentationKey) {
				unset($projects[$i]);
				$found = TRUE;
				break;
			}
		}

		if ($found) {
			return $this->persistProjects($projects);
		}
		return FALSE;
	}

	/**
	 * Instantiate a Project domain object from raw data.
	 *
	 * @param array $data
	 * @return \Causal\Sphinx\Domain\Model\Project
	 */
	protected function instantiateProjectFromArray(array $data) {
		/** @var \Causal\Sphinx\Domain\Model\Project $project */
		$project = GeneralUtility::makeInstance('Causal\\Sphinx\\Domain\\Model\\Project', $data['key']);
		$project->setName($data['name']);
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
	protected function loadProjects() {
		$projects = array();
		$filename = GeneralUtility::getFileAbsFileName(static::PROJECTS_FILENAME);
		if (is_file($filename)) {
			$contents = file_get_contents($filename);
			$projects = json_decode($contents, TRUE);
			if (!is_array($projects)) {
				$projects = array();
			}
		}
		return $projects;
	}

	/**
	 * Persists the projects.
	 *
	 * @param array $projects
	 * @return bool TRUE if the list of projects was successfully persisted
	 */
	protected function persistProjects(array $projects) {
		$filename = GeneralUtility::getFileAbsFileName(static::PROJECTS_FILENAME);
		if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
			$content = json_encode($projects, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
		} else {
			$content = json_encode($projects);
			if (count($projects) > 0) {
				// Mimic JSON_UNESCAPE_SLASHES
				$content = str_replace('\\/', '/', $content);
				// Mimic JSON_PRETTY_PRINT (for our known data structure)
				$content = "{\n\t" . substr($content, 1, -1) . "\n}";
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
