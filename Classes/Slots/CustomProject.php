<?php
namespace Causal\Sphinx\Slots;

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

	/** @var string */
	static protected $extKey = 'sphinx';

	/**
	 * @var \Causal\Sphinx\Domain\Repository\ProjectRepository
	 * @inject
	 */
	protected $projectRepository;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * Default constructor.
	 */
	public function __construct() {
		$this->settings = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][static::$extKey]);
	}

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
		$absoluteBasePath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($basePath);
		$warningsFilename = $absoluteBasePath . 'warnings.txt';

		if (is_file($absoluteBasePath . 'source/conf.py')) {
			// Separate source/build directories
			$sourceDirectory = 'source/';
			$buildDirectory = 'build/';
			$confFilename = 'source/conf.py';
		} else {
			// TYPO3 documentation project
			$sourceDirectory = '.';
			$buildDirectory = '_make/build/';
			$confFilename = '_make/conf.py';
		}

		try {
			switch ($layout) {
				case 'html':        // Static
					$masterFile = $buildDirectory . 'html/Index.html';
					if ($force || !is_file($absoluteBasePath . $masterFile)) {
						if (is_file($warningsFilename)) {
							@unlink($warningsFilename);
						}
						\Causal\Sphinx\Utility\SphinxBuilder::buildHtml(
							$absoluteBasePath,
							$sourceDirectory,
							$buildDirectory,
							$confFilename
						);
						if (is_file($warningsFilename) && filesize($warningsFilename) > 0) {
							copy($warningsFilename, $absoluteBasePath . $buildDirectory . 'html/warnings.txt');
						}
					}
					$documentationUrl = '../' . $basePath . $masterFile;
				break;
				case 'json':        // Interactive
					$masterFile = $buildDirectory . 'json/Index.fjson';
					if ($force || !is_file($absoluteBasePath . $masterFile)) {
						$configurationFilename = $absoluteBasePath . $confFilename;
						$backupConfigurationFilename = $configurationFilename . '.bak';
						if (copy($configurationFilename, $backupConfigurationFilename)) {
							if ($confFilename === '_make/conf.py') {
								$settingsYamlFilename = $absoluteBasePath . 'Settings.yml';
								\Causal\Sphinx\Utility\GeneralUtility::overrideThemeT3Sphinx($absoluteBasePath);
								if (is_file($settingsYamlFilename)) {
									$confpyFilename = $absoluteBasePath . $confFilename;
									$confpy = file_get_contents($confpyFilename);
									$pythonConfiguration = \Causal\Sphinx\Utility\GeneralUtility::yamlToPython($settingsYamlFilename);
									$confpy .= LF . '# Additional options from Settings.yml' . LF . implode(LF, $pythonConfiguration);
									\TYPO3\CMS\Core\Utility\GeneralUtility::writeFile($confpyFilename, $confpy);
								}
							}

							if (is_file($warningsFilename)) {
								@unlink($warningsFilename);
							}
							\Causal\Sphinx\Utility\SphinxBuilder::buildJson(
								$absoluteBasePath,
								$sourceDirectory,
								$buildDirectory,
								$confFilename
							);
							if (is_file($warningsFilename) && filesize($warningsFilename) > 0) {
								copy($warningsFilename, $absoluteBasePath . $buildDirectory . 'json/warnings.txt');
							}

							if (file_exists($backupConfigurationFilename)) {
								// Replace special-crafted conf.py by the backup version
								rename($backupConfigurationFilename, $configurationFilename);
							}
						}
					}
					$documentationUrl = '../' . $basePath . $masterFile;
				break;
				case 'pdf':
					switch ($this->settings['pdf_builder']) {
						case 'pdflatex':
							$masterFilePattern = $buildDirectory . 'latex/*.pdf';
							$targetWarnings = 'latex/warnings.txt';
							break;
						case 'rst2pdf':
						default:
							$masterFilePattern = $buildDirectory . 'pdf/*.pdf';
							$targetWarnings = 'pdf/warnings.txt';
							break;
					}

					$availablePdfs = glob($absoluteBasePath . $masterFilePattern);
					if ($force || count($availablePdfs) == 0) {
						if (is_file($warningsFilename)) {
							@unlink($warningsFilename);
						}
						\Causal\Sphinx\Utility\SphinxBuilder::buildPdf(
							$absoluteBasePath,
							$sourceDirectory,
							$buildDirectory,
							$confFilename
						);
						if (is_file($warningsFilename) && filesize($warningsFilename) > 0) {
							copy($warningsFilename, $absoluteBasePath . $buildDirectory . $targetWarnings);
						}
						$availablePdfs = glob($absoluteBasePath . $masterFilePattern);
					}
					$documentationUrl = '../' . substr($availablePdfs[0], strlen(PATH_site));
				break;
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

		// Automatically fix Intersphinx mapping, if needed
		$settingsYamlFilename = $absoluteBasePath . rtrim($sourceDirectory, '/') . '/Settings.yml';
		if (is_file($warningsFilename) && is_file($settingsYamlFilename) && is_writable($settingsYamlFilename)) {
			if (\Causal\Sphinx\Utility\GeneralUtility::autofixMissingIntersphinxMapping($warningsFilename, $settingsYamlFilename)) {
				// Recompile and hope this works this time!
				$this->render($identifier, $layout, $force, $documentationUrl);
			}
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

		$absoluteBasePath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($directory);
		if (is_file($absoluteBasePath . 'source/conf.py')) {
			// Separate source/build directories
			$buildDirectory = 'build/json/';
		} else {
			// TYPO3 documentation project
			$buildDirectory = '_make/build/json/';
		}

		$path = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($directory . $buildDirectory);
	}

	/**
	 * Returns the .rst filename for a given document.
	 *
	 * @param string $identifier
	 * @param string $document
	 * @param string &$basePath
	 * @param string &$filename
	 * @return void
	 */
	public function retrieveRestFilename($identifier, $document, &$basePath, &$filename) {
		$projects = $this->projectRepository->findAll();
		$directory = $projects[$identifier]->getDirectory();

		$absoluteBasePath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($directory);
		if (is_file($absoluteBasePath . 'source/conf.py')) {
			// Separate source/build directories
			$directory = rtrim($directory, '/') . '/source/';
		}

		$jsonFilename = substr($document, 0, strlen($document) - 1) . '.rst';
		$basePath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($directory);
		$filename = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($directory . $jsonFilename);
	}

}
