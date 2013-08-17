<?php
namespace Causal\Sphinx\Utility;

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

use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * General utility.
 *
 * @category    Utility
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class GeneralUtility {

	const DOCUMENTATION_TYPE_UNKNOWN    = 0;
	const DOCUMENTATION_TYPE_SPHINX     = 1;
	const DOCUMENTATION_TYPE_README     = 2;
	const DOCUMENTATION_TYPE_OPENOFFICE = 3;

	/** @var string */
	static protected $extKey = 'sphinx';

	/**
	 * Returns meta-data for a given extension.
	 *
	 * @param string $extensionKey
	 * @return array
	 */
	static public function getExtensionMetaData($extensionKey) {
		$_EXTKEY = $extensionKey;
		$EM_CONF = array();
		$extPath = ExtensionManagementUtility::extPath($extensionKey);
		include($extPath . 'ext_emconf.php');

		$release = $EM_CONF[$_EXTKEY]['version'];
		list($major, $minor, $_) = explode('.', $release, 3);
		if (($pos = strpos($minor, '-')) !== FALSE) {
			// $minor ~ '2-dev'
			$minor = substr($minor, 0, $pos);
		}
		$EM_CONF[$_EXTKEY]['version'] = $major . '.' . $minor;
		$EM_CONF[$_EXTKEY]['release'] = $release;
		$EM_CONF[$_EXTKEY]['extensionKey'] = $extensionKey;

		return $EM_CONF[$_EXTKEY];
	}

	/**
	 * Returns the type of the master documentation document of a given
	 * loaded extension as one of the DOCUMENTATION_TYPE_* constants.
	 *
	 * @param string $extensionKey
	 * @return integer DOCUMENTATION_TYPE_* constant
	 */
	static public function getDocumentationType($extensionKey) {
		$supportedDocuments = array(
			'Documentation/Index.rst' => self::DOCUMENTATION_TYPE_SPHINX,
			'README.rst'              => self::DOCUMENTATION_TYPE_README,
			'doc/manual.sxw'          => self::DOCUMENTATION_TYPE_OPENOFFICE,
		);
		$extPath = ExtensionManagementUtility::extPath($extensionKey);

		foreach ($supportedDocuments as $supportedDocument => $type) {
			if (is_file($extPath . $supportedDocument)) {
				return $type;
			}
		}

		return self::DOCUMENTATION_TYPE_UNKNOWN;
	}

	/**
	 * Returns an array of localization directories along with the
	 * mapping to an official locale supported by Sphinx.
	 *
	 * @param string $extensionKey
	 * @return array
	 * @see \Causal\Sphinx\Utility\SphinxBuilder::getSupportedLocales()
	 */
	static public function getLocalizationDirectories($extensionKey) {
		static $localizationDirectories = array();

		if (!isset($localizationDirectories[$extensionKey])) {
			$localizationDirectories[$extensionKey] = array();

			$pattern = 'Documentation/Localization.*';
			$supportedLocales = \Causal\Sphinx\Utility\SphinxBuilder::getSupportedLocales();
			$extPath = ExtensionManagementUtility::extPath($extensionKey);
			$directories = glob($extPath . $pattern);

			foreach ($directories as $directory) {
				$directory = substr($directory, strlen($extPath));
				if (preg_match('#Documentation/Localization\.([a-z]{2})_([A-Z]{2})$#', $directory, $matches)) {
					$localizationLocale = $matches[1] . '_' . $matches[2];

					foreach ($supportedLocales as $locale => $_) {
						if (strpos($locale, '_') === FALSE && $matches[1] === $locale) {
							$localizationDirectories[$extensionKey][$locale] = array(
								'directory' => $directory,
								'locale' => $localizationLocale,
							);
							$localizationDirectories[$extensionKey][$localizationLocale] = array(
								'directory' => $directory,
								'locale' => $localizationLocale,
							);
							break;
						} elseif ($localizationLocale === $locale) {
							$localizationDirectories[$extensionKey][$locale] = array(
								'directory' => $directory,
								'locale' => $localizationLocale,
							);
							break;
						}
					}
				}
			}
		}

		return $localizationDirectories[$extensionKey];
	}

	/**
	 * Returns the type of the master documentation document, localized
	 * in a given language/locale, of a given loaded extension as one of
	 * the DOCUMENTATION_TYPE_* constants.
	 *
	 * @param string $extensionKey
	 * @param string $locale
	 * @return integer DOCUMENTATION_TYPE_* constant
	 */
	static public function getLocalizedDocumentationType($extensionKey, $locale) {
		$localizationDirectories = self::getLocalizationDirectories($extensionKey);

		if (isset($localizationDirectories[$locale])) {
			$extPath = ExtensionManagementUtility::extPath($extensionKey);
			if (is_file($extPath . $localizationDirectories[$locale]['directory'] . '/Index.rst')) {
				return self::DOCUMENTATION_TYPE_SPHINX;
			}
		}

		return self::DOCUMENTATION_TYPE_UNKNOWN;
	}

	/**
	 * Returns the documentation project title for a given extension.
	 *
	 * @param string $extensionKey
	 * @param string $locale
	 * @return string
	 */
	static public function getDocumentationProjectTitle($extensionKey, $locale = '') {
		$projectTitle = '';

		if (empty($locale)) {
			$settingsFilename = 'Documentation/Settings.yml';
		} else {
			$localizationDirectories = self::getLocalizationDirectories($extensionKey);
			if (!isset($localizationDirectories[$locale])) {
				return $projectTitle;
			}
			$settingsFilename = $localizationDirectories[$locale]['directory'] . '/Settings.yml';
		}
		$extPath = ExtensionManagementUtility::extPath($extensionKey);

		if (is_file($extPath . $settingsFilename)) {
			$settings = file_get_contents($extPath . $settingsFilename);
			if (preg_match('/^\s+project:\s*(.*)$/m', $settings, $matches)) {
				$projectTitle = trim($matches[1]);
			}
		}

		return $projectTitle;
	}

	/**
	 * Post-processes the property tables.
	 *
	 * @param string $contents
	 * @return string
	 * @see https://forge.typo3.org/issues/48771
	 */
	static public function postProcessPropertyTables($contents) {
		$contents = preg_replace_callback('#<div class="table-row container">.<dl class="docutils">(.*?)</dl>.</div>#s', function ($tableRow) {
			$cellCounter = 0;
			$propertyTable = preg_replace_callback('#<dt>(.*?)</dt>.<dd>(.*?)</dd>#s', function ($tableCell) use (&$cellCounter) {
				switch (++$cellCounter) {
					case 1:
						$cellName = 't3-cell-property';
						break;
					case 2:
						$cellName = 't3-cell-datatype';
						break;
					case 3:
						$cellName = 't3-cell-description';
						break;
					default:
						$cellName = 't3-cell-unknown';
						break;
				}

				$term = $tableCell[1];
				$definition = $tableCell[2];
				if (substr($definition, 0, 2) !== '<p') {
					$definition = '<p>' . $definition . '</p>';
				}

				return <<<HTML
<div class="t3-cell $cellName">
	<p class="term">$term</p>
	$definition
</div>
HTML;
			}, $tableRow[1]);

			$propertyTable = <<<HTML
<div class="t3-row table-row container">
	$propertyTable
	<div class="cc container"></div>
</div>
HTML;

			return $propertyTable;
		}, $contents);

		return $contents;
	}

	/**
	 * Populate the list of labels for cross-referencing because package t3sphinx
	 * is not (yet?) compatible with JSON rendering and thus its directive
	 * ".. ref-targets-list::" is bypassed.
	 *
	 * @param string $contents
	 * @param array $references
	 * @param callback $callbackLinks Callback to generate Links in current context
	 * @return string
	 * @throws \RuntimeException
	 * @see http://forge.typo3.org/issues/48313
	 */
	static public function populateCrossReferencingLabels($contents, array $references, $callbackLinks) {
		$callableName = '';
		if (!is_callable($callbackLinks, FALSE, $callableName)) {
			throw new \RuntimeException('Invalid callback for links: ' . $callableName, 1376471476);
		}

		if (preg_match('#(.*)(<div class="section"[^>]*>.<span id="labels-for-crossreferencing"></span>[^\n]*.)([^\n]+)(.*)#s', $contents, $matches)) {
			// Pattern matches:
			// #1: beginning up to:
			// #2: <div id="index-labels-for-cross-referencing" class="section">
			//     <span id="labels-for-crossreferencing"></span><h1>Index: Labels for Cross-referencing</h1>
			// #3: </div>
			// #4: to the end
			if ($matches[3] === '</div>') {
				$listOfLabels = array();
				$listOfLabels[] = '<dl class="ref-targets-list docutils">';

				// Clean up references
				foreach (array('0', 'search', 'py-modindex') as $file) {
					unset($references[$file]);
				}

				// Move 1st-level references at the beginning
				$tempReferences = array();
				foreach ($references as $file => $anchors) {
					if (strpos($file, '/') === FALSE) {
						$tempReferences[$file] = $anchors;
					}
				}
				$references = array_merge($tempReferences, array_diff_key($references, $tempReferences));

				foreach ($references as $file => $anchors) {
					$listOfLabels[] = '<dt>' . htmlspecialchars($file) . '</dt>';
					$listOfLabels[] = '<dd><ul class="first last simple">';

					// Prepare retrieval of line numbers for anchors
					$lines = array();
					$isGeneralIndex = ($file === 'genindex');
					if (!$isGeneralIndex) {
						$source = '_sources/' . $file . '.txt';
						$filename = call_user_func($callbackLinks, $source);
						$absoluteFilename = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName(substr($filename, 3));
						if (is_file($absoluteFilename)) {
							$fileContents = file_get_contents($absoluteFilename);
							$lines = explode(LF, $fileContents);
						}
					}

					foreach ($anchors as $anchor) {
						$lineNumber = 1;
						for ($i = 0; $i < count($lines); $i++) {
							if (preg_match('/^\s*\.\. _`?' . preg_quote($anchor['name']) . '`?:/', $lines[$i])) {
								$lineNumber = $i + 1;
								break;
							}
						}

						if (!$isGeneralIndex) {
							$source = '_sources/' . substr($anchor['link'], 0, strrpos($anchor['link'], '/')) . '.txt';
							$sourceUrl = call_user_func($callbackLinks, $source);
							$sourceUrl .= '?refid=start&line=' . $lineNumber;
							$sourceUrl = str_replace('&amp;', '&', $sourceUrl);
							$sourceUrl = str_replace('&', '&amp;', $sourceUrl);

							$sourceLink = '<a href="' . $sourceUrl . '" class="e2 reference internal">' . sprintf('%04d', $lineNumber) . '</a>';
						} else {
							$sourceLink = sprintf('%04d', $lineNumber);
						}

						$document = str_replace('$', $anchor['name'], $anchor['link']);
						$documentUrl = call_user_func($callbackLinks, $document);
						$documentUrl = str_replace('&amp;', '&', $documentUrl);
						$documentUrl = str_replace('&', '&amp;', $documentUrl);

						$sourceLinkPattern =

						$listOfLabels[] = '<li><span class="e1">[</span>' .
							$sourceLink .
							'<span class="e3">]</span> ' .
							'<a title="' . htmlspecialchars($anchor['title']) . '" href="' . $documentUrl . '" class="e4 reference internal">' .
								':ref:`' . htmlspecialchars($anchor['name']) . '`' .
							'</a></li>';
					}
					$listOfLabels[] = '</ul>';
				}
				$listOfLabels[] = '</dl>';
				$contents = $matches[1] . $matches[2] . implode(LF, $listOfLabels) . $matches[3] . $matches[4];
			}
		}

		return $contents;
	}

	/**
	 * Returns the intersphinx references of a given extension.
	 *
	 * @param string $extensionKey
	 * @return array
	 * @throws \RuntimeException
	 */
	static public function getIntersphinxReferences($extensionKey) {
		if (!ExtensionManagementUtility::isLoaded('restdoc')) {
			throw new \RuntimeException('Extension restdoc is not loaded', 1370809705);
		}

		$localFile = PATH_site . 'typo3conf/Documentation/' . $extensionKey . '/default/json/objects.inv';
		$cacheFile = PATH_site . 'typo3temp/tx_' . self::$extKey . '/' . $extensionKey . '/_make/build/json/objects.inv';
		$remoteUrl = 'http://docs.typo3.org/typo3cms/extensions/' . $extensionKey . '/latest/objects.inv';
		$path = '';

		if (is_file($localFile)) {
			$path = dirname($localFile);
		} elseif (is_file($cacheFile)) {
			$path = dirname($cacheFile);
		} else {
			$content = \TYPO3\CMS\Core\Utility\GeneralUtility::getUrl($remoteUrl);
			if ($content) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::mkdir_deep(dirname($cacheFile) . '/');
				\TYPO3\CMS\Core\Utility\GeneralUtility::writeFile($cacheFile, $content);
				$path = dirname($cacheFile);
			}
		}

		if ($path) {
			/** @var \Tx_Restdoc_Reader_SphinxJson $sphinxReader */
			$sphinxReader = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx_Restdoc_Reader_SphinxJson');
			$sphinxReader->setPath($path);
			$references = $sphinxReader->getReferences();
		} else {
			$references = array();
		}

		return $references;
	}

	/**
	 * Generates the documentation for a given extension.
	 *
	 * @param string $extensionKey
	 * @param string $format
	 * @param boolean $force
	 * @param string $locale
	 * @return string The documentation URL
	 */
	static public function generateDocumentation($extensionKey, $format = 'html', $force = FALSE, $locale = '') {
		if (empty($locale)) {
			$documentationType = self::getDocumentationType($extensionKey);
			$projectTitle = self::getDocumentationProjectTitle($extensionKey);
			$languageDirectory = 'default';
		} else {
			$documentationType = self::getLocalizedDocumentationType($extensionKey, $locale);
			$projectTitle = self::getDocumentationProjectTitle($extensionKey, $locale);
			$languageDirectory = $locale;
		}
		if (!($documentationType === self::DOCUMENTATION_TYPE_SPHINX
			|| $documentationType === self::DOCUMENTATION_TYPE_README)) {

			$filename = 'typo3temp/tx_' . self::$extKey . '/1369679343.log';
			$content = 'ERROR 1369679343: No documentation found for extension "' . $extensionKey . '"';
			\TYPO3\CMS\Core\Utility\GeneralUtility::writeFile(PATH_site . $filename, $content);
			return '../' . $filename;
		}

		switch ($format) {
			case 'json':
				$documentationFormat = 'json';
				$masterDocument = 'Index.fjson';
				break;
			case 'pdf':
				$documentationFormat = 'pdf';
				$masterDocument = $extensionKey . '.pdf';
				break;
			case 'html':
			default:
				$documentationFormat = 'html';
				$masterDocument = 'Index.html';
				break;
		}

		$relativeOutputDirectory = 'typo3conf/Documentation/typo3cms.extensions.' . $extensionKey . '/' . $languageDirectory . '/' . $documentationFormat;
		$absoluteOutputDirectory = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($relativeOutputDirectory);
		if (!$force && is_file($absoluteOutputDirectory . '/' . $masterDocument)) {
			// Do not render the documentation again
			$documentationUrl = '../' . $relativeOutputDirectory . '/' . $masterDocument;
			return $documentationUrl;
		}

		$metadata = GeneralUtility::getExtensionMetaData($extensionKey);
		$basePath = PATH_site . 'typo3temp/tx_' . self::$extKey . '/' . $extensionKey;
		$documentationBasePath = $basePath;
		\TYPO3\CMS\Core\Utility\GeneralUtility::rmdir($basePath, TRUE);
		if (!empty($locale)) {
			$documentationBasePath .= '/Localization.' . $locale;
		}

		SphinxQuickstart::createProject(
			$documentationBasePath,
			$projectTitle ?: $metadata['title'],
			$metadata['author'],
			FALSE,
			'TYPO3DocEmptyProject',
			$metadata['version'],
			$metadata['release'],
			$extensionKey
		);

		// Recursively instantiate template files
		switch ($documentationType) {
			case self::DOCUMENTATION_TYPE_SPHINX:
				$source = ExtensionManagementUtility::extPath($extensionKey) . 'Documentation';
				self::recursiveCopy($source, $basePath);

				// Remove Localization.* directories to prevent clash with references
				// @see https://forge.typo3.org/issues/51066
				if (empty($locale)) {
					$localizationDirectories = self::getLocalizationDirectories($extensionKey);
					foreach ($localizationDirectories as $info) {
						$localizationDirectory = $basePath . DIRECTORY_SEPARATOR . basename($info['directory']);
						if (is_dir($localizationDirectory)) {
							\TYPO3\CMS\Core\Utility\GeneralUtility::rmdir($localizationDirectory, TRUE);
						}
					}
				}
				break;
			case self::DOCUMENTATION_TYPE_README:
				$source = ExtensionManagementUtility::extPath($extensionKey) . 'README.rst';
				copy($source, $basePath . '/Index.rst');
		}

		if ($format === 'json') {
			self::overrideThemeT3Sphinx($documentationBasePath);
			$settingsYamlFilename = $documentationBasePath . '/Settings.yml';
			if (is_file($settingsYamlFilename)) {
				$confpyFilename = $documentationBasePath . '/_make/conf.py';
				$confpy = file_get_contents($confpyFilename);
				$pythonConfiguration = self::yamlToPython($settingsYamlFilename);
				$confpy .= LF . '# Additional options from Settings.yml' . LF . implode(LF, $pythonConfiguration);
				\TYPO3\CMS\Core\Utility\GeneralUtility::writeFile($confpyFilename, $confpy);
			}
		}

		try {
			if ($format === 'json') {
				SphinxBuilder::buildJson($documentationBasePath, '.', '_make/build', '_make/conf.py', $locale);
			} elseif ($format === 'pdf') {
				SphinxBuilder::buildPdf($documentationBasePath, '.', '_make/build', '_make/conf.py', $locale);
			} else {
				SphinxBuilder::buildHtml($documentationBasePath, '.', '_make/build', '_make/conf.py', $locale);
			}
		} catch (\RuntimeException $e) {
			$relativeFilename = 'typo3temp/tx_' . self::$extKey . '/' . $e->getCode() . '.log';
			$absoluteFilename = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($relativeFilename);
			$content = $e->getMessage();
			\TYPO3\CMS\Core\Utility\GeneralUtility::writeFile($absoluteFilename, $content);
			return '../' . $relativeFilename;
		}

		\TYPO3\CMS\Core\Utility\GeneralUtility::rmdir($absoluteOutputDirectory, TRUE);
		\TYPO3\CMS\Core\Utility\GeneralUtility::mkdir_deep($absoluteOutputDirectory . '/');
		if ($format !== 'pdf') {
			self::recursiveCopy($documentationBasePath . '/_make/build/' . $documentationFormat, $absoluteOutputDirectory);
		} else {
			// Only copy PDF output
			$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][self::$extKey]);
			switch ($configuration['pdf_builder']) {
				case 'pdflatex':
					copy($documentationBasePath . '/_make/build/latex/' . $extensionKey . '.pdf', $absoluteOutputDirectory . '/' . $extensionKey . '.pdf');
					break;
				case 'rst2pdf':
					copy($documentationBasePath . '/_make/build/pdf/' . $extensionKey . '.pdf', $absoluteOutputDirectory . '/' . $extensionKey . '.pdf');
					break;
			}
		}

		$documentationUrl = '../' . $relativeOutputDirectory . '/' . $masterDocument;
		return $documentationUrl;
	}

	/**
	 * Creates a special-crafted conf.py for JSON output when using
	 * t3sphinx as HTML theme.
	 *
	 * @param string $basePath
	 * @return void
	 * @see \Causal\Sphinx\Controller\ConsoleController::overrideThemeT3Sphinx()
	 * @see http://forge.typo3.org/issues/48311
	 */
	static public function overrideThemeT3Sphinx($basePath) {
		$configuration = file_get_contents($basePath . '/_make/conf.py');
		$t3sphinxImportPattern = '/^(\s*)import\s+t3sphinx\s*$/m';

		if (preg_match($t3sphinxImportPattern, $configuration, $matches)) {
			$imports = array(
				'from docutils.parsers.rst import directives',
				'from t3sphinx import fieldlisttable',
				'directives.register_directive(\'t3-field-list-table\', fieldlisttable.FieldListTable)',
			);
			$replacement = $matches[1] . implode(LF . $matches[1], $imports);
			$newConfiguration = preg_replace($t3sphinxImportPattern, $replacement, $configuration);

			\TYPO3\CMS\Core\Utility\GeneralUtility::writeFile($basePath . '/_make/conf.py', $newConfiguration);
		}
	}

	/**
	 * Recursively copy content from one directory to another.
	 *
	 * @param string $source
	 * @param string $target
	 * @return void
	 */
	static public function recursiveCopy($source, $target) {
		$target = rtrim($target, '/');
		/** @var \RecursiveDirectoryIterator $iterator */
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($source,
				\RecursiveDirectoryIterator::SKIP_DOTS),
			\RecursiveIteratorIterator::SELF_FIRST
		);
		foreach ($iterator as $item) {
			/** @var \splFileInfo $item */
			if ($item->isDir()) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::mkdir($target . '/' . $iterator->getSubPathName());
			} else {
				copy($item, $target . '/' . $iterator->getSubPathName());
			}
		}
	}

	/**
	 * Returns a command to export a value to the environment variables.
	 *
	 * Important: if $variable is found in $value (with the '$' prefix as
	 *            needed by Unix-like OS), it will be rewritten for the
	 *            current OS.
	 *
	 * @param string $variable
	 * @param string $value
	 * @return string
	 */
	static public function getExportCommand($variable, $value) {
		if (TYPO3_OS === 'WIN') {
			$pattern = 'SET %s=%s';
			$value = preg_replace('/\$' . $variable . '([^A-Za-z]|$)/', '%' . $variable . '%', $value);
		} else {
			$pattern = 'export %s=%s';
		}
		return sprintf($pattern, $variable, $value);
	}

	/**
	 * Converts a (simple) YAML file to Python instructions.
	 *
	 * Note: First tried to use 3rd party libraries:
	 *
	 * - spyc: http://code.google.com/p/spyc/
	 * - Symfony2 YAML: http://symfony.com/doc/current/components/yaml/introduction.html
	 *
	 * but none of them were able to parse our Settings.yml Sphinx configuration files.
	 *
	 * @param string $filename
	 * @return string
	 */
	static public function yamlToPython($filename) {
		$contents = file_get_contents($filename);
		$lines = explode(LF, $contents);
		$pythonConfiguration = array();

		$i = 0;
		while ($lines[$i] !== 'conf.py:' && $i < count($lines)) {
			$i++;
		}
		while ($i < count($lines)) {
			$i++;
			if (preg_match('/^(\s+)([^:]+):\s*(.*)$/', $lines[$i], $matches)) {
				$pythonLine = '';
				switch ($matches[2]) {
					case 'latex_documents':
						$pythonLine = 'latex_documents = [(' . LF;
						if (preg_match('/^(\s+)- - /', $lines[$i + 1], $matches)) {
							$indent = $matches[1];
							$firstLine = TRUE;
							while (preg_match('/^' . $indent . '(- -|  -) (.+)$/', $lines[++$i], $matches)) {
								if (!$firstLine) {
									$pythonLine .= ',' . LF;
								}
								$pythonLine .= sprintf('u\'%s\'', addcslashes($matches[2], "\\'"));
								$firstLine = FALSE;
							}
						}
						$pythonLine .= LF . ')]';
						$i--;
						break;
					case 'latex_elements':
						$pythonLine = 'latex_elements = {' . LF;
						if (preg_match('/^(\s+)/', $lines[$i + 1], $matches)) {
							$indent = $matches[1];
							$firstLine = TRUE;
							while (preg_match('/^' . $indent . '([^:]+):\s*(.*)$/', $lines[++$i], $matches)) {
								if (!$firstLine) {
									$pythonLine .= ',' . LF;
								}
								$pythonLine .= sprintf('\'%s\': \'%s\'', $matches[1], addcslashes($matches[2], "\\'"));
								$firstLine = FALSE;
							}
						}
						$pythonLine .= LF . '}';
						$i--;
						break;
					case 'intersphinx_mapping':
						$pythonLine = 'intersphinx_mapping = {' . LF;
						if (preg_match('/^(\s+)/', $lines[$i + 1], $matches)) {
							$indent = $matches[1];
							$firstLine = TRUE;
							while (preg_match('/^' . $indent . '(.+):/', $lines[++$i], $matches)) {
								if (!$firstLine) {
									$pythonLine .= ',' . LF;
								}
								$pythonLine .= sprintf('\'%s\': (', $matches[1]);
								$firstItem = TRUE;
								while (preg_match('/^' . $indent . '- (.+)/', $lines[++$i], $matches)) {
									if (!$firstItem) {
										$pythonLine .= ', ';
									}
									if ($matches[1] === 'null') {
										$pythonLine .= 'None';
									} else {
										$pythonLine .= sprintf('\'%s\'', $matches[1]);
									}
									$firstItem = FALSE;
								}
								$pythonLine .= ')';
								$firstLine = FALSE;
								$i--;
							}
						}
						$pythonLine .= LF . '}';
						$i--;
						break;
					default:
						$pythonLine = sprintf('%s = u\'%s\'', $matches[2], addcslashes($matches[3], "\\'"));
						break;
				}
				if (!empty($pythonLine)) {
					$pythonConfiguration[] = $pythonLine;
				}
			}
		}

		return $pythonConfiguration;
	}

}

?>