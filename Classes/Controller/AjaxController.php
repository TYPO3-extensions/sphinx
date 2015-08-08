<?php
namespace Causal\Sphinx\Controller;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\CommandUtility;
use Causal\Sphinx\Utility\GitUtility;
use Causal\Sphinx\Utility\MiscUtility;

/**
 * AJAX controller.
 *
 * @category    Controller
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class AjaxController extends AbstractActionController
{

    /**
     * @var \Causal\Sphinx\Domain\Repository\ProjectRepository
     * @inject
     */
    protected $projectRepository;

    /**
     * @var \Causal\Sphinx\Domain\Repository\DocumentationRepository
     * @inject
     */
    protected $documentationRepository;

    /**
     * Returns a form to add a custom project.
     *
     * @return void
     */
    public function addCustomProjectAction()
    {
        $isGitAvailable = GitUtility::isAvailable();
        $this->view->assign('hasGit', $isGitAvailable);

        $locales = \Causal\Sphinx\Utility\SphinxBuilder::getSupportedLocales();
        asort($locales);
        $locales = array('' => $this->translate('language.default')) + $locales;

        $projectTemplates = $this->getProjectTemplates();
        $templates = array();

        if ($isGitAvailable) {
            $officialDocuments = $this->documentationRepository->getOfficialDocuments();

            $gitDocuments = array();
            foreach ($officialDocuments as $officialDocument) {
                if (!empty($officialDocument['git'])) {
                    $officialDocument['type'] = 'TYPO3 ' . $officialDocument['type'];
                    $masterKey = $officialDocument['type'];
                    $key = $officialDocument['key'];
                    $templates[$masterKey][$key] = $officialDocument['title'];
                    $gitDocuments[$key] = $officialDocument;

                    // Sort by title
                    asort($templates[$masterKey]);
                }
            }
            // Sort by type
            ksort($templates);
        }

        // Prepend with custom project templates
        $templates = array($this->translate('dashboard.action.label.customProject') => $projectTemplates) + $templates;

        $this->view->assignMultiple(array(
            'locales' => $locales,
            'templates' => $templates,
        ));

        $response = array();
        $response['status'] = 'success';
        $response['statusText'] = $this->view->render();

        if ($isGitAvailable) {
            $officialDocuments = json_encode($gitDocuments);
            $response['js'] = <<<JS
CausalSphinxDashboard.officialDocuments = $officialDocuments;
JS;
        }

        $this->returnAjax($response);
    }

    /**
     * Creates a custom project.
     *
     * @param string $group
     * @param string $name
     * @param string $lang
     * @param string $description
     * @param string $documentationKey
     * @param string $directory
     * @param string $template
     * @param string $git
     * @return void
     */
    public function createCustomProjectAction($group, $name, $lang, $description, $documentationKey, $directory, $template = '', $git = '')
    {
        $response = array();
        $success = false;
        $isGitAvailable = GitUtility::isAvailable();
        $mayCloneFromGit = false;

        // Sanitize directory and documentation key
        $directory = str_replace('\\', '/', $directory);
        $directory = rtrim($directory, '/') . '/';
        $documentationKey = strtolower(trim($documentationKey));

        if (!$this->isValidDocumentationKey($documentationKey)) {
            $response['status'] = 'error';
            $response['statusText'] = $this->translate('dashboard.action.error.invalidDocumentationKey');
            $this->returnAjax($response);
        }

        $existingProject = $this->projectRepository->findByDocumentationKey($documentationKey);

        if ($existingProject === null && $isGitAvailable && !empty($git)) {
            $mayCloneFromGit = GitUtility::isValidRepository($git);
            if (!$mayCloneFromGit) {
                $response['status'] = 'error';
                $response['statusText'] = $this->translate('dashboard.action.error.invalidGitRepository');
                $this->returnAjax($response);
            }
        }

        $projectStructure = MiscUtility::getProjectStructure($directory);
        if ($mayCloneFromGit) {
            $absoluteDirectory = GeneralUtility::getFileAbsFileName($directory);
            if ($projectStructure !== MiscUtility::PROJECT_STRUCTURE_UNKNOWN || is_dir($absoluteDirectory . '.git')) {
                $response['status'] = 'error';
                $response['statusText'] = $this->translate('dashboard.action.error.directoryNotEmpty');
                $this->returnAjax($response);
            }

            GeneralUtility::mkdir_deep($absoluteDirectory);
            GitUtility::cloneRepository($git, $absoluteDirectory, '.');

            // Try to discover project structure again
            $projectStructure = MiscUtility::getProjectStructure($directory);
            if ($projectStructure === MiscUtility::PROJECT_STRUCTURE_UNKNOWN) {
                // Maybe a TYPO3 project after all, but anyway, make sure project will be registered and let
                // the user fix herself any possible problem with the git clone
                $projectStructure = MiscUtility::PROJECT_STRUCTURE_TYPO3;

                if (is_dir($absoluteDirectory . 'Documentation')) {
                    $directory .= 'Documentation/';
                }
            }
        } elseif ($existingProject === null && !empty($template)) {
            $absoluteDirectory = GeneralUtility::getFileAbsFileName($directory);
            GeneralUtility::mkdir_deep($absoluteDirectory);

            \Causal\Sphinx\Utility\SphinxQuickstart::createProject(
                $absoluteDirectory,
                $name,
                $GLOBALS['BE_USER']->user['realName'],
                strpos($template, 'Separate') !== false,
                $template
            );

            // Discover project structure again
            $projectStructure = MiscUtility::getProjectStructure($directory);
        }

        if ($projectStructure !== MiscUtility::PROJECT_STRUCTURE_UNKNOWN) {
            // $existingProject must be null otherwise it means we try to reuse an existing project's key
            if ($existingProject === null) {
                /** @var \Causal\Sphinx\Domain\Model\Project $project */
                $project = GeneralUtility::makeInstance('Causal\\Sphinx\\Domain\\Model\\Project', $documentationKey);
                $project->setName($name);
                $project->setLanguage($lang);
                $project->setDescription($description);
                $project->setGroup($group);
                $project->setDirectory($directory);

                $success = $this->projectRepository->add($project);
                if (!$success) {
                    $response['statusText'] = $this->translate('dashboard.action.error.unknownError');
                }
            } else {
                $response['statusText'] = $this->translate('dashboard.action.error.invalidDocumentationKey');
            }
        } else {
            $response['statusText'] = $this->translate('dashboard.action.error.invalidDirectory');
        }

        if ($success) {
            $response['status'] = 'success';
        } else {
            $response['status'] = 'error';
        }

        $this->returnAjax($response);
    }

    /**
     * Returns a form to edit a custom project.
     *
     * @param string $documentationKey
     * @return void
     */
    public function editCustomProjectAction($documentationKey)
    {
        $response = array();

        $locales = \Causal\Sphinx\Utility\SphinxBuilder::getSupportedLocales();
        asort($locales);
        $locales = array('' => $this->translate('language.default')) + $locales;

        $project = $this->projectRepository->findByDocumentationKey($documentationKey);

        if ($project !== null) {
            $this->view->assignMultiple(array(
                'project' => $project,
                'locales' => $locales,
            ));
            $response['status'] = 'success';
            $response['statusText'] = $this->view->render();
        } else {
            $response['status'] = 'error';
        }

        $this->returnAjax($response);
    }

    /**
     * Updates a custom project.
     *
     * @param string $group
     * @param string $name
     * @param string $lang
     * @param string $description
     * @param string $documentationKey
     * @param string $originalDocumentationKey
     * @param string $directory
     * @param bool $updateGroup
     * @return void
     */
    public function updateCustomProjectAction($group, $name, $lang, $description, $documentationKey,
                                              $originalDocumentationKey, $directory, $updateGroup)
    {
        $response = array();
        $success = false;

        // Sanitize directory and documentation key
        $directory = str_replace('\\', '/', $directory);
        $directory = rtrim($directory, '/') . '/';
        $documentationKey = strtolower(trim($documentationKey));

        $projectStructure = MiscUtility::getProjectStructure($directory);
        if ($projectStructure !== MiscUtility::PROJECT_STRUCTURE_UNKNOWN) {
            $project = $this->projectRepository->findByDocumentationKey($originalDocumentationKey);
            if ($originalDocumentationKey !== $documentationKey) {
                $existingProject = $this->projectRepository->findByDocumentationKey($documentationKey);
            } else {
                $existingProject = null;
            }

            // $existingProject must be null otherwise it means we try to reuse an existing project's key
            if ($this->isValidDocumentationKey($documentationKey) && $project !== null && $existingProject === null) {
                $previousGroup = $project->getGroup();

                $project->setGroup($group);
                $project->setName($name);
                $project->setLanguage($lang);
                $project->setDescription($description);
                $project->setDocumentationKey($documentationKey);
                $project->setDirectory($directory);

                $success = $this->projectRepository->update($project);
                if ($success && $updateGroup) {
                    $success = $this->projectRepository->renameGroup($previousGroup, $group);
                }
                if (!$success) {
                    $response['statusText'] = $this->translate('dashboard.action.error.unknownError');
                }
            } else {
                $response['statusText'] = $this->translate('dashboard.action.error.invalidDocumentationKey');
            }
        } else {
            $response['statusText'] = $this->translate('dashboard.action.error.invalidDirectory');
        }

        if ($success) {
            $response['status'] = 'success';
        } else {
            $response['status'] = 'error';
        }

        $this->returnAjax($response);
    }

    /**
     * Removes a custom project.
     *
     * @param string $documentationKey Reference of a custom project
     * @return void
     */
    public function removeCustomProjectAction($documentationKey)
    {
        $response = array();

        if ($this->projectRepository->remove($documentationKey)) {
            $response['status'] = 'success';
        } else {
            $response['status'] = 'error';
            $response['statusText'] = $this->translate('dashboard.action.error.unknownError');
        }

        $this->returnAjax($response);
    }

    /**
     * Checks if a given documentation key has the correct format.
     *
     * @param string $documentationKey
     * @return bool
     */
    protected function isValidDocumentationKey($documentationKey)
    {
        return preg_match('/^[a-z][a-z0-9]*(\.[a-z0-9]+)*$/', $documentationKey);
    }

    /**
     * Returns the available project templates.
     *
     * @return array
     */
    protected function getProjectTemplates()
    {
        $templates = array();
        foreach (array('BlankSingleProject', 'BlankSeparateProject', 'TYPO3DocProject') as $key) {
            $templates[$key] = $this->translate('dashboard.projectTemplates.' . $key);
        }
        return $templates;
    }

}
