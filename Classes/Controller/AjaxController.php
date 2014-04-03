<?php
namespace Causal\Sphinx\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Xavier Perseguers <xavier@causal.ch>
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
class AjaxController extends AbstractActionController {

	/**
	 * @var \Causal\Sphinx\Domain\Repository\ProjectRepository
	 * @inject
	 */
	protected $projectRepository;

	/**
	 * Returns a form to add a custom project.
	 *
	 * @return void
	 */
	protected function addCustomProjectAction() {
		$locales = \Causal\Sphinx\Utility\SphinxBuilder::getSupportedLocales();
		asort($locales);
		$locales = array('' => $this->translate('language.default')) + $locales;

		$this->view->assign('locales', $locales);

		$response = array();
		$response['status'] = 'success';
		$response['statusText'] = $this->view->render();

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
	 * @return void
	 */
	protected function createCustomProjectAction($group, $name, $lang, $description, $documentationKey, $directory) {
		$response = array();
		$success = FALSE;

		// Sanitize directory
		$directory = rtrim($directory, '/') . '/';

		$projectStructure = MiscUtility::getProjectStructure($directory);
		if ($projectStructure !== MiscUtility::PROJECT_STRUCTURE_UNKNOWN) {
			$existingProject = $this->projectRepository->findByDocumentationKey($documentationKey);

			// $existingProject must be NULL otherwise it means we try to reuse an existing project's key
			if ($existingProject === NULL) {
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
	protected function editCustomProjectAction($documentationKey) {
		$response = array();

		$locales = \Causal\Sphinx\Utility\SphinxBuilder::getSupportedLocales();
		asort($locales);
		$locales = array('' => $this->translate('language.default')) + $locales;

		$project = $this->projectRepository->findByDocumentationKey($documentationKey);

		if ($project !== NULL) {
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
	protected function updateCustomProjectAction($group, $name, $lang, $description, $documentationKey,
												 $originalDocumentationKey, $directory, $updateGroup) {
		$response = array();
		$success = FALSE;

		// Sanitize directory
		$directory = rtrim($directory, '/') . '/';

		$projectStructure = MiscUtility::getProjectStructure($directory);
		if ($projectStructure !== MiscUtility::PROJECT_STRUCTURE_UNKNOWN) {
			$project = $this->projectRepository->findByDocumentationKey($originalDocumentationKey);
			if ($originalDocumentationKey !== $documentationKey) {
				$existingProject = $this->projectRepository->findByDocumentationKey($documentationKey);
			} else {
				$existingProject = NULL;
			}

			// $existingProject must be NULL otherwise it means we try to reuse an existing project's key
			if ($project !== NULL && $existingProject === NULL) {
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
	protected function removeCustomProjectAction($documentationKey) {
		$response = array();

		if ($this->projectRepository->remove($documentationKey)) {
			$response['status'] = 'success';
		} else {
			$response['status'] = 'error';
			$response['statusText'] = $this->translate('dashboard.action.error.unknownError');
		}

		$this->returnAjax($response);
	}

}
