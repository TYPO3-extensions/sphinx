<?php
namespace Causal\Sphinx\Slots;

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
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use Causal\Sphinx\Utility\MiscUtility;

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
		$project = $this->projectRepository->findByDocumentationKey($identifier);
		if ($project === NULL) {
			return;
		}

		$basePath = $project->getDirectory();
		$absoluteBasePath = GeneralUtility::getFileAbsFileName($basePath);
		$warningsFilename = $absoluteBasePath . 'warnings.txt';

		$projectStructure = MiscUtility::getProjectStructure($absoluteBasePath);
		switch ($projectStructure) {
			case MiscUtility::PROJECT_STRUCTURE_SINGLE:
				$sourceDirectory = '.';
				$buildDirectory = '_build/';
				$confFilename = './conf.py';
				break;
			case MiscUtility::PROJECT_STRUCTURE_SEPARATE:
				$sourceDirectory = 'source/';
				$buildDirectory = 'build/';
				$confFilename = 'source/conf.py';
				break;
			case MiscUtility::PROJECT_STRUCTURE_TYPO3:
			default:
				$sourceDirectory = '.';
				$buildDirectory = '_make/build/';
				$confFilename = '_make/conf.py';

				if (!is_dir($absoluteBasePath . '_make')) {
					// Prepare the project so that it may be properly rendered
					GeneralUtility::mkdir($absoluteBasePath . '_make');

					/** @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $contentObj */
					$contentObj = GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer');

					$projectName = str_replace(' ', '', $project->getName());
					$markers = array(
						'PROJECT'            => $projectName,
						'PROJECT_NAME'       => $projectName,
						'CURRENT_DATE'       => date('r'),
						'YEAR'               => date('Y'),
						'MASTER_DOCUMENT'    => 'Index',
						'PATH_TEMPLATES'     => '_templates',
						'PATH_STATIC'        => '_static',
						'SOURCE_FILE_SUFFIX' => '.rst',
						'EXCLUDE_PATTERN'    => '_make',
					);

					$confPyTemplate = ExtensionManagementUtility::extPath(static::$extKey) . 'Resources/Private/Templates/Projects/TYPO3DocEmptyProject/_make/conf.py.tmpl';

					$contents = file_get_contents($confPyTemplate);
					('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer');
					$contents = $contentObj->substituteMarkerArray($contents, $markers, '###|###');
					GeneralUtility::writeFile($absoluteBasePath . '_make/conf.py', $contents);
				}
				break;
		}

		$configurationFilename = $absoluteBasePath . $confFilename;
		$backupConfigurationFilename = $configurationFilename . '.bak';
		if (!copy($configurationFilename, $backupConfigurationFilename)) {
			throw new \RuntimeException('Could not pre-process configuration file "' . $configurationFilename . '"', 1423582193);
		}

		if ($confFilename === '_make/conf.py') {
			$settingsYamlFilename = $absoluteBasePath . 'Settings.yml';
			MiscUtility::overrideThemeT3Sphinx($absoluteBasePath);
			if (is_file($settingsYamlFilename)) {
				$confpy = file_get_contents($configurationFilename);
				$pythonConfiguration = MiscUtility::yamlToPython($settingsYamlFilename);
				$confpy .= LF . '# Additional options from Settings.yml' . LF . implode(LF, $pythonConfiguration);
				GeneralUtility::writeFile($configurationFilename, $confpy);
			}
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
							$confFilename,
							$project->getLanguage()
						);
						$targetWarningsFilename = $absoluteBasePath . $buildDirectory . 'html/warnings.txt';
						if (is_file($warningsFilename) && filesize($warningsFilename) > 0) {
							copy($warningsFilename, $targetWarningsFilename);
						} elseif (is_file($targetWarningsFilename)) {
							@unlink($targetWarningsFilename);
						}
					}
					$documentationUrl = '../' . $basePath . $masterFile;
				break;
				case 'json':        // Interactive
					$masterFile = $buildDirectory . 'json/Index.fjson';
					if ($force || !is_file($absoluteBasePath . $masterFile)) {
						if (is_file($warningsFilename)) {
							@unlink($warningsFilename);
						}
						\Causal\Sphinx\Utility\SphinxBuilder::buildJson(
							$absoluteBasePath,
							$sourceDirectory,
							$buildDirectory,
							$confFilename,
							$project->getLanguage()
						);
						$targetWarningsFilename = $absoluteBasePath . $buildDirectory . 'json/warnings.txt';
						if (is_file($warningsFilename) && filesize($warningsFilename) > 0) {
							copy($warningsFilename, $targetWarningsFilename);
						} elseif (is_file($targetWarningsFilename)) {
							@unlink($targetWarningsFilename);
						}
					}
					$documentationUrl = '../' . $basePath . $masterFile;
				break;
				case 'pdf':
					switch ($this->settings['pdf_builder']) {
						case 'pdflatex':
							$masterFilePattern = $buildDirectory . 'latex/*.pdf';
							$targetWarningsFilename = $absoluteBasePath . $buildDirectory . 'latex/warnings.txt';
							break;
						case 'rst2pdf':
						default:
							$masterFilePattern = $buildDirectory . 'pdf/*.pdf';
							$targetWarningsFilename = $absoluteBasePath . $buildDirectory . 'pdf/warnings.txt';
							break;
					}

					$availablePdfs = glob($absoluteBasePath . $masterFilePattern);
					if ($availablePdfs === FALSE) {
						// An error occured
						$availablePdfs = array();
					}
					if ($force || count($availablePdfs) == 0) {
						if (is_file($warningsFilename)) {
							@unlink($warningsFilename);
						}
						\Causal\Sphinx\Utility\SphinxBuilder::buildPdf(
							$absoluteBasePath,
							$sourceDirectory,
							$buildDirectory,
							$confFilename,
							$project->getLanguage()
						);
						if (is_file($warningsFilename) && filesize($warningsFilename) > 0) {
							copy($warningsFilename, $targetWarningsFilename);
						} elseif (is_file($targetWarningsFilename)) {
							@unlink($targetWarningsFilename);
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
			switch ($e->getCode()) {
				case 1366210198:	// Sphinx is not configured
				case 1366280021:	// Sphinx cannot be executed
					$emLink = MiscUtility::getExtensionManagerLink('sphinx', 'Configuration', 'showConfigurationForm');
					$templateContent = <<<HTML
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Exception</title>
  </head>
  <body>
    <pre>###CONTENT###</pre>
    <p><a href="$emLink" target="_parent">Click here</a> to configure the sphinx extension.</p>
  </body>
</html>
HTML;
					$extensionFileName = '.html';
					break;
				default:
					$templateContent = '###CONTENT###';
					$extensionFileName = '.log';
					break;
			}
			$fileName = 'typo3temp/tx_myext_' . $e->getCode() . $extensionFileName;
			$content = str_replace('###CONTENT###', $e->getMessage(), $templateContent);
			GeneralUtility::writeFile(PATH_site . $fileName, $content);
			$documentationUrl = '../' . $fileName;
		}

		if (file_exists($backupConfigurationFilename)) {
			// Replace special-crafted conf.py by the backup version
			rename($backupConfigurationFilename, $configurationFilename);
		}

		// Automatically fix Intersphinx mapping, if needed
		$settingsYamlFilename = $absoluteBasePath . rtrim($sourceDirectory, '/') . '/Settings.yml';
		if (is_file($warningsFilename) && is_file($settingsYamlFilename) && is_writable($settingsYamlFilename)) {
			if (MiscUtility::autofixMissingIntersphinxMapping($warningsFilename, $settingsYamlFilename)) {
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
		$project = $this->projectRepository->findByDocumentationKey($identifier);
		$directory = $project->getDirectory();

		$projectStructure = MiscUtility::getProjectStructure($directory);
		switch ($projectStructure) {
			case MiscUtility::PROJECT_STRUCTURE_SINGLE:
				$buildDirectory = '_build/json/';
				break;
			case MiscUtility::PROJECT_STRUCTURE_SEPARATE:
				$buildDirectory = 'build/json/';
				break;
			case MiscUtility::PROJECT_STRUCTURE_TYPO3:
			default:
				$buildDirectory = '_make/build/json/';
				break;
		}

		$path = GeneralUtility::getFileAbsFileName($directory . $buildDirectory);
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
		$project = $this->projectRepository->findByDocumentationKey($identifier);
		$directory = $project->getDirectory();

		$projectStructure = MiscUtility::getProjectStructure($directory);
		switch ($projectStructure) {
			case MiscUtility::PROJECT_STRUCTURE_SEPARATE:
				$directory = rtrim($directory, '/') . '/source/';
				break;
		}

		$jsonFilename = substr($document, 0, strlen($document) - 1) . '.rst';
		$basePath = GeneralUtility::getFileAbsFileName($directory);
		$filename = GeneralUtility::getFileAbsFileName($directory . $jsonFilename);
	}

}
