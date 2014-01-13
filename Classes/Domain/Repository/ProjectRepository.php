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

use TYPO3\CMS\Core\Utility\GeneralUtility as CoreGeneralUtility;

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
			/** @var \Causal\Sphinx\Domain\Model\Project $project */
			$project = CoreGeneralUtility::makeInstance('Causal\\Sphinx\\Domain\\Model\\Project');
			$project->setDocumentationKey($p['key']);
			$project->setName($p['name']);
			$project->setDescription($p['description']);
			$project->setGroup($p['group']);
			$project->setDirectory($p['directory']);
			$projects[$project->getDocumentationKey()] = $project;
		}
		return $projects;
	}

	/**
	 * Loads the available projects.
	 *
	 * @return array
	 */
	protected function loadProjects() {
		$projects = array();
		$filename = CoreGeneralUtility::getFileAbsFileName(static::PROJECTS_FILENAME);
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
	 * @return void
	 */
	protected function persistProjects(array $projects) {
		$filename = CoreGeneralUtility::getFileAbsFileName(static::PROJECTS_FILENAME);
		CoreGeneralUtility::writeFile($filename, json_encode($projects));
	}

}
