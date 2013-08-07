<?php
namespace Causal\Sphinx\Slots;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Xavier Perseguers <xavier@causal.ch>
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

/**
 * Slot implementation for EXT:sphinx.
 *
 * @category    Slots
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class CustomProject {

	/**
	 * @var \Causal\Sphinx\Domain\Repository\ProjectRepository
	 * @inject
	 */
	protected $projectRepository;

	/**
	 * Registers the documentation.
	 *
	 * @param array &$references
	 * @return void
	 */
	public function postprocessReferences(array &$references) {
		$projects = $this->projectRepository->findAll();
		foreach ($projects as $project) {
			$group = $project->getGroup();
			$reference = 'USER:' . $project->getDocumentationKey();
			$references[$group][$reference] = $project->getName();
			ksort($references[$group]);
		}
		ksort($references);
	}

	/**
	 * Renders the documentation.
	 *
	 * @param string $identifier
	 * @param string $layout
	 * @param boolean $force
	 * @param string &$documentationUrl
	 * @return void
	 */
	public function render($identifier, $layout, $force, &$documentationUrl) {
		$projects = $this->projectRepository->findAll();
		if (!isset($projects[$identifier])) {
			return;
		}

		$basePath = $projects[$identifier]->getDirectory();
		$buildDirectory = '_make/build/';
		$confFilename = '_make/conf.py';

		try {
			switch ($layout) {
				case 'html':        // Static
					$masterFile = '_make/build/html/Index.html';
					if ($force || !is_file($basePath . $masterFile)) {
						\Causal\Sphinx\Utility\SphinxBuilder::buildHtml(
							\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($basePath),
							'.',
							$buildDirectory,
							$confFilename
						);
					}
					$documentationUrl = '../' . $basePath . $masterFile;
					break;
				case 'json':        // Interactive
					$masterFile = '_make/build/json/Index.fjson';
					if ($force || !is_file($basePath . $masterFile)) {
						$configurationFilename = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($basePath . $confFilename);
						$backupConfigurationFilename = $configurationFilename . '.bak';
						if (copy($configurationFilename, $backupConfigurationFilename)) {
							\Causal\Sphinx\Utility\GeneralUtility::overrideThemeT3Sphinx(
								\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($basePath)
							);

							\Causal\Sphinx\Utility\SphinxBuilder::buildJson(
								\TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($basePath),
								'.',
								$buildDirectory,
								$confFilename
							);

							if (file_exists($backupConfigurationFilename)) {
								// Replace special-crafted conf.py by the backup version
								rename($backupConfigurationFilename, $configurationFilename);
							}
						}
					}
					$documentationUrl = '../' . $basePath . $masterFile;
					break;
				case 'pdf':
				default:
					throw new \RuntimeException(
						'Sorry! Layout ' . $layout . ' is not yet supported', 1371415095
					);
			}
		} catch (\RuntimeException $e) {
			$filename = 'typo3temp/tx_myext_' . $e->getCode() . '.log';
			$content = $e->getMessage();
			\TYPO3\CMS\Core\Utility\GeneralUtility::writeFile(PATH_site . $filename, $content);
			$documentationUrl = '../' . $filename;
		}
	}

	/**
	 * Returns the base path for a given project identifier.
	 *
	 * @param string $identifier
	 * @param string &$path
	 * @return void
	 */
	public function retrieveBasePath($identifier, &$path) {
		$projects = $this->projectRepository->findAll();
		$directory = $projects[$identifier]->getDirectory();
		$buildDirectory = '_make/build/json/';
		$path = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($directory . $buildDirectory);
	}

	/**
	 * Returns the .rst filename for a given document.
	 *
	 * @param string $identifier
	 * @param string $document
	 * @param string &$filename
	 * @return void
	 */
	public function retrieveRestFilename($identifier, $document, &$filename) {
		$projects = $this->projectRepository->findAll();
		$directory = $projects[$identifier]->getDirectory();
		$jsonFilename = substr($document, 0, strlen($document) - 1) . '.rst';
		$filename = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($directory . $jsonFilename);
	}

}

?>