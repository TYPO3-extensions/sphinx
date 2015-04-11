<?php
namespace Causal\Sphinx\Utility;

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
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * Miscellaneous utility.
 *
 * @category    Utility
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class MiscUtility {

	const PROJECT_STRUCTURE_UNKNOWN     = 0;
	const PROJECT_STRUCTURE_SINGLE      = 1;
	const PROJECT_STRUCTURE_SEPARATE    = 2;
	const PROJECT_STRUCTURE_TYPO3       = 3;

	// Constants below are power of 2 so that they may be combined
	const DOCUMENTATION_TYPE_UNKNOWN    = 0;
	const DOCUMENTATION_TYPE_SPHINX     = 1;
	const DOCUMENTATION_TYPE_README     = 2;
	const DOCUMENTATION_TYPE_OPENOFFICE = 4;

	/** @var string */
	static protected $extKey = 'sphinx';

	/** @var \TYPO3\CMS\Extensionmanager\Utility\ListUtility */
	static protected $listUtility;

	/**
	 * Returns meta-data for a given extension.
	 *
	 * @param string $extensionKey The TYPO3 extension key
	 * @return array
	 */
	static public function getExtensionMetaData($extensionKey) {
		$_EXTKEY = $extensionKey;
		$EM_CONF = array();
		$extPath = static::extPath($extensionKey);
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

		// Uncommon:
		$EM_CONF[$_EXTKEY]['siteRelPath'] = substr($extPath, strlen(PATH_site));
		$EM_CONF[$_EXTKEY]['ext_icon'] = ExtensionManagementUtility::getExtensionIcon($extPath);

		if (GeneralUtility::isFirstPartOfStr($EM_CONF[$_EXTKEY]['siteRelPath'], 'typo3conf/ext/')) {
			$EM_CONF[$_EXTKEY]['type'] = 'L';
		} elseif (GeneralUtility::isFirstPartOfStr($EM_CONF[$_EXTKEY]['siteRelPath'], 'typo3/sysext/')) {
			$EM_CONF[$_EXTKEY]['type'] = 'S';
		} else {
			$EM_CONF[$_EXTKEY]['type'] = 'G';
		}

		return $EM_CONF[$_EXTKEY];
	}

	/**
	 * Returns the type of project found in directory $path as one of the
	 * PROJECT_STRUCTURE_* constants.
	 *
	 * @param string $path Relative or absolute path
	 * @return integer One of the PROJECT_STRUCTURE_* constants
	 */
	static public function getProjectStructure($path) {
		$type = static::PROJECT_STRUCTURE_UNKNOWN;

		// To deal with both relative and absolute $path
		$path = str_replace('\\', '/', $path);
		$absolutePath = GeneralUtility::getFileAbsFileName(rtrim($path, '/') . '/');

		if (is_file($absolutePath . 'conf.py')) {
			// All in one directory
			$type = static::PROJECT_STRUCTURE_SINGLE;
		} elseif (is_file($absolutePath . 'source/conf.py')) {
			// Separate source/build directories
			$type = static::PROJECT_STRUCTURE_SEPARATE;
		} elseif (is_file($absolutePath . 'Index.rst')) {
			// TYPO3 documentation project
			$type = static::PROJECT_STRUCTURE_TYPO3;
		}

		return $type;
	}

	/**
	 * Returns the types of the documentation document(s) of a given loaded extension
	 * as a binary combination of the DOCUMENTATION_TYPE_* constants.
	 *
	 * @param string $extensionKey The TYPO3 extension key
	 * @return integer Binary combination of the DOCUMENTATION_TYPE_* constants
	 */
	static public function getDocumentationTypes($extensionKey) {
		$supportedDocuments = array(
			'Documentation/Index.rst' => static::DOCUMENTATION_TYPE_SPHINX,
			'README.rst'              => static::DOCUMENTATION_TYPE_README,
			'doc/manual.sxw'          => static::DOCUMENTATION_TYPE_OPENOFFICE,
		);
		$extPath = static::extPath($extensionKey);

		$types = static::DOCUMENTATION_TYPE_UNKNOWN;
		foreach ($supportedDocuments as $supportedDocument => $type) {
			if (is_file($extPath . $supportedDocument)) {
				$types |= $type;
			}
		}

		return $types;
	}

	/**
	 * Returns an array of localization directories along with the
	 * mapping to an official locale supported by Sphinx.
	 *
	 * @param string $extensionKey The TYPO3 extension key
	 * @return array Array of localization directories (relative to the extension's directory)
	 * @see \Causal\Sphinx\Utility\SphinxBuilder::getSupportedLocales()
	 */
	static public function getLocalizationDirectories($extensionKey) {
		static $localizationDirectories = array();

		if (!isset($localizationDirectories[$extensionKey])) {
			$localizationDirectories[$extensionKey] = array();

			$pattern = 'Documentation/Localization.*';
			$supportedLocales = \Causal\Sphinx\Utility\SphinxBuilder::getSupportedLocales();
			$extPath = static::extPath($extensionKey);
			$directories = glob($extPath . $pattern);
			if ($directories === FALSE) {
				// An error occured
				$directories = array();
			}

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
	 * @param string $extensionKey The TYPO3 extension key
	 * @param string $locale The locale to use
	 * @return integer One of the DOCUMENTATION_TYPE_* constants
	 */
	static public function getLocalizedDocumentationType($extensionKey, $locale) {
		$localizationDirectories = static::getLocalizationDirectories($extensionKey);

		if (isset($localizationDirectories[$locale])) {
			$extPath = static::extPath($extensionKey);
			if (is_file($extPath . $localizationDirectories[$locale]['directory'] . '/Index.rst')) {
				return static::DOCUMENTATION_TYPE_SPHINX;
			}
		}

		return static::DOCUMENTATION_TYPE_UNKNOWN;
	}

	/**
	 * Returns the documentation project title for a given extension.
	 *
	 * @param string $extensionKey The TYPO3 extension key
	 * @param string $locale The locale to use
	 * @return string The project title
	 */
	static public function getDocumentationProjectTitle($extensionKey, $locale = '') {
		$projectTitle = '';

		if (empty($locale)) {
			$settingsFilename = 'Documentation/Settings.yml';
		} else {
			$localizationDirectories = static::getLocalizationDirectories($extensionKey);
			if (!isset($localizationDirectories[$locale])) {
				return $projectTitle;
			}
			$settingsFilename = $localizationDirectories[$locale]['directory'] . '/Settings.yml';
		}
		$extPath = static::extPath($extensionKey);

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
	 * @param string $contents Contents to be processed
	 * @return string Contents with processed property tables
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
	 * @param @param string $contents Contents to be processed
	 * @param array $references Array of references (extracted from objects.inv)
	 * @param callback $callbackLinks Callback to generate Links in current context
	 * @return string Processed contents
	 * @throws \RuntimeException
	 * @see MiscUtility::getIntersphinxReferences()
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
			// #2: <div class="section" id="index-labels-for-cross-referencing">
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
						$absoluteFilename = GeneralUtility::getFileAbsFileName(substr($filename, 3));
						if (is_file($absoluteFilename)) {
							$fileContents = file_get_contents($absoluteFilename);
							$lines = explode(LF, $fileContents);
						}
					}

					foreach ($anchors as $anchor) {
						$lineNumber = 1;
						$numberOfLines = count($lines);
						for ($i = 0; $i < $numberOfLines; $i++) {
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
	 * Returns a fully qualified reference from a given intersphinx key
	 * (official documents solely).
	 *
	 * @param string $intersphinxKey
	 * @param array &$additionalData
	 * @return string
	 */
	static public function getReferenceFromIntersphinxKey($intersphinxKey, array &$additionalData = NULL) {
		// No dependency injection needed here
		/** @var \Causal\Sphinx\Domain\Repository\DocumentationRepository $documentationRepository */
		$documentationRepository = GeneralUtility::makeInstance('Causal\\Sphinx\\Domain\\Repository\\DocumentationRepository');
		$officialDocuments = $documentationRepository->getOfficialDocuments();

		// Not an official "document" but still...
		$officialDocuments[] = array(
			'key' => 'typo3cms.api.t3cmsapi',
			'shortcut' => 't3cmsapi',
			'url' => 'http://typo3.org/api/typo3cms/',
		);

		$reference = NULL;
		foreach ($officialDocuments as $officialDocument) {
			if ($officialDocument['shortcut'] === $intersphinxKey) {
				$reference = $officialDocument['key'];
				$additionalData = $officialDocument;
				break;
			}
		}

		return $reference;
	}

	/**
	 * Converts an intersphinx key to an extension key.
	 *
	 * We do this by looking for a mapping between all existing extension keys (without
	 * underscore character '_' and the given intersphinx key).
	 *
	 * As of 19.04.2014, it is known that it fails to disambiguate two extensions:
	 * "bzd_staff_directory" and "bzdstaffdirectory" but this later is the real one
	 * because bzd_staff_directory was abandonned and "recreated" as bzdstaffdirectory
	 * when version 0.8.0 came out. By ordering by last_updated, we ensure the most
	 * recent extension will take precedence.
	 *
	 * @param string $intersphinxKey
	 * @return string
	 */
	static public function intersphinxKeyToExtensionKey($intersphinxKey) {
		/** @var \TYPO3\CMS\Core\Database\DatabaseConnection $databaseConnection */
		$databaseConnection = $GLOBALS['TYPO3_DB'];

		// We filter the query by using the first two letters of the intersphinx key
		// or first letter and an underscore
		//
		// In the worst case, this query:
		//
		// SELECT SUBSTR(extension_key, 1, 2), COUNT(*) FROM (
		//     SELECT DISTINCT extension_key FROM tx_extensionmanager_domain_model_extension
		// ) as tmp
		// GROUP BY SUBSTR(extension_key, 1, 2)
		//
		// shows that about 120 rows containing a single (short) string will be returned.
		// The additional condition "WHERE extension_key LIKE '%\_%'" has not been added
		// because it did not seem to be really significant to trim down the list even more.
		$table = 'tx_extensionmanager_domain_model_extension';
		$rows = $databaseConnection->exec_SELECTgetRows(
			'DISTINCT extension_key',
			$table,
			'extension_key LIKE ' . $databaseConnection->fullQuoteStr(
				$databaseConnection->escapeStrForLike(substr($intersphinxKey, 0, 2), $table) . '%',
				$table
			) . ' OR extension_key LIKE ' . $databaseConnection->fullQuoteStr(
				$databaseConnection->escapeStrForLike($intersphinxKey{0} . '_', $table) . '%',
				$table
			),
			'',
			'last_updated'
		);

		$mapping = array();
		foreach ($rows as $row) {
			$key = str_replace('_', '', $row['extension_key']);
			$mapping[$key] = $row['extension_key'];
		}

		return isset($mapping[$intersphinxKey]) ? $mapping[$intersphinxKey] : $intersphinxKey;
	}

	/**
	 * Returns the Intersphinx references of a given documentation reference.
	 *
	 * @param string $reference Reference of a documentation or an extension key
	 * @param string $locale The locale to use
	 * @param string &$remoteUrl The remote URL to retrieve objects.inv (as reference!)
	 * @param string $localFilename The local objects.inv filename to read
	 * @return array Intersphinx references extracted from objects.inv
	 * @throws \RuntimeException
	 */
	static public function getIntersphinxReferences($reference, $locale = '', &$remoteUrl = '', $localFilename = '') {
		if (!ExtensionManagementUtility::isLoaded('restdoc')) {
			throw new \RuntimeException('Extension restdoc is not loaded', 1370809705);
		}

		if (strpos($reference, '.') === FALSE) {
			// Extension key has been provided
			$extensionKey = $reference;
			$reference = 'typo3cms.extensions.' . $extensionKey;
			$cacheFile = GeneralUtility::getFileAbsFileName(
				'typo3temp/tx_' . static::$extKey . '/' . $extensionKey . '/_make/build/json/objects.inv'
			);
			$remoteUrl = 'http://docs.typo3.org/typo3cms/extensions/' . $extensionKey;
			if ($locale) {
				$remoteUrl .= '/' . strtolower(str_replace('_', '-', $locale));
			}
		} else {
			$cacheFile = GeneralUtility::getFileAbsFileName(
				'typo3temp/tx_' . static::$extKey . '/' . $reference . '/objects.inv'
			);
		}
		$remoteFilename = $remoteUrl;
		if ($remoteFilename && substr($remoteFilename, -12) !== '/objects.inv') {
			$remoteFilename = rtrim($remoteFilename, '/') . '/objects.inv';
		}

		if (empty($localFilename)) {
			$localFilename = GeneralUtility::getFileAbsFileName(
				'typo3conf/Documentation/' . $reference . '/' . ($locale ?: 'default') . '/json/objects.inv'
			);
		}
		$path = '';

		$useCache = TRUE;
		if ($remoteFilename && is_file($cacheFile) && $GLOBALS['EXEC_TIME'] - filemtime($cacheFile) > 86400) {
			// Cache file is more than 1 day old and we have an URL to fetch a fresh version: DO IT!
			$useCache = FALSE;
		}

		if (is_file($localFilename)) {
			$path = PathUtility::dirname($localFilename);
		} elseif ($useCache && is_file($cacheFile)) {
			$path = PathUtility::dirname($cacheFile);
		} elseif ($remoteFilename) {
			$content = static::getUrl($remoteFilename);
			if ($content) {
				GeneralUtility::mkdir_deep(PathUtility::dirname($cacheFile) . '/');
				GeneralUtility::writeFile($cacheFile, $content);
				$path = PathUtility::dirname($cacheFile);
			}
		}

		if ($path) {
			/** @var \Tx_Restdoc_Reader_SphinxJson $sphinxReader */
			$sphinxReader = GeneralUtility::makeInstance('Tx_Restdoc_Reader_SphinxJson');
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
	 * @param string $extensionKey The TYPO3 extension key
	 * @param string $format The format of the documentation ("html", "json" or "pdf")
	 * @param boolean $force TRUE if generation should be forced, otherwise FALSE to use cached version, if available
	 * @param string $locale The locale to use
	 * @return string The documentation URL
	 */
	static public function generateDocumentation($extensionKey, $format = 'html', $force = FALSE, $locale = '') {
		$originalExtensionKey = $extensionKey;

		if (empty($locale)) {
			$lengthSuffixReadme = strlen(\Causal\Sphinx\Domain\Repository\ExtensionRepository::SUFFIX_README);
			if (substr($extensionKey, -$lengthSuffixReadme) === \Causal\Sphinx\Domain\Repository\ExtensionRepository::SUFFIX_README) {
				$documentationTypes = static::DOCUMENTATION_TYPE_README;
				$extensionKey = substr($extensionKey, 0, -$lengthSuffixReadme);
			} else {
				$documentationTypes = $type ?: static::getDocumentationTypes($extensionKey);
			}
			$projectTitle = static::getDocumentationProjectTitle($extensionKey);
			$languageDirectory = 'default';
		} else {
			$documentationTypes = static::getLocalizedDocumentationType($extensionKey, $locale);
			$projectTitle = static::getDocumentationProjectTitle($extensionKey, $locale);
			$languageDirectory = $locale;
		}
		if (!($documentationTypes & static::DOCUMENTATION_TYPE_SPHINX
			|| $documentationTypes & static::DOCUMENTATION_TYPE_README)) {

			$filename = 'typo3temp/tx_' . static::$extKey . '/1369679343.log';
			$content = 'ERROR 1369679343: No documentation found for extension "' . $extensionKey . '"';
			GeneralUtility::writeFile(PATH_site . $filename, $content);
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

		$relativeOutputDirectory = 'typo3conf/Documentation/typo3cms.extensions.' . $originalExtensionKey . '/' . $languageDirectory . '/' . $documentationFormat;
		$absoluteOutputDirectory = GeneralUtility::getFileAbsFileName($relativeOutputDirectory);
		if (!$force && is_file($absoluteOutputDirectory . '/' . $masterDocument)) {
			// Do not render the documentation again
			$documentationUrl = '../' . $relativeOutputDirectory . '/' . $masterDocument;
			return $documentationUrl;
		}

		$metadata = static::getExtensionMetaData($extensionKey);
		$basePath = PATH_site . 'typo3temp/tx_' . static::$extKey . '/' . $extensionKey;
		$documentationBasePath = $basePath;
		GeneralUtility::rmdir($basePath, TRUE);
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
		switch (TRUE) {
			case $documentationTypes & static::DOCUMENTATION_TYPE_SPHINX:
				$source = static::extPath($extensionKey) . 'Documentation';
				static::recursiveCopy($source, $basePath);

				// Remove Localization.* directories to prevent clash with references
				// @see https://forge.typo3.org/issues/51066
				if (empty($locale)) {
					$localizationDirectories = static::getLocalizationDirectories($extensionKey);
					foreach ($localizationDirectories as $info) {
						$localizationDirectory = $basePath . DIRECTORY_SEPARATOR . PathUtility::basename($info['directory']);
						if (is_dir($localizationDirectory)) {
							GeneralUtility::rmdir($localizationDirectory, TRUE);
						}
					}
				}

				// Remove *.tmpl directories
				// @see http://forge.typo3.org/issues/59356
				$templateDirectories = array_filter(
					GeneralUtility::get_dirs($source),
					function ($directory) {
						return substr($directory, -5) === '.tmpl';
					}
				);
				foreach ($templateDirectories as $directory) {
					GeneralUtility::rmdir($basePath . DIRECTORY_SEPARATOR . $directory, TRUE);
				}
			break;
			case $documentationTypes & static::DOCUMENTATION_TYPE_README:
				$extensionPath = static::extPath($extensionKey);
				$source = $extensionPath . 'README.rst';
				copy($source, $basePath . '/Index.rst');
				$resourceDirectories = array('Documentation', 'Resources');
				foreach ($resourceDirectories as $resourceDirectory) {
					if (is_dir($extensionPath . $resourceDirectory)) {
						GeneralUtility::mkdir($basePath . '/' . $resourceDirectory);
						static::recursiveCopy($extensionPath . $resourceDirectory, $basePath . '/' . $resourceDirectory);
					}
				}
			break;
		}

		// Cache Intersphinx references to speed-up rendering
		$settingsYamlFilename = $documentationBasePath . '/Settings.yml';
		if (is_file($settingsYamlFilename)) {
			static::cacheIntersphinxMapping($documentationBasePath . '/Settings.yml');
		}

		// Theme t3sphinx is still incompatible with JSON output
		if ($format === 'json') {
			static::overrideThemeT3Sphinx($documentationBasePath);
			if (is_file($settingsYamlFilename)) {
				$confpyFilename = $documentationBasePath . '/_make/conf.py';
				$confpy = file_get_contents($confpyFilename);
				$pythonConfiguration = static::yamlToPython($settingsYamlFilename);
				$confpy .= LF . '# Additional options from Settings.yml' . LF . implode(LF, $pythonConfiguration);
				GeneralUtility::writeFile($confpyFilename, $confpy);
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
			switch ($e->getCode()) {
				case 1366210198:	// Sphinx is not configured
				case 1366280021:	// Sphinx cannot be executed
					$emLink = static::getExtensionManagerLink('sphinx', 'Configuration', 'showConfigurationForm');
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
					$templateContent = <<<HTML
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Exception</title>
    <style type="text/css">
    pre {
      width: 80em;

      white-space: -moz-pre-wrap; /* Mozilla, supported since 1999 */
      white-space: -pre-wrap; /* Opera */
      white-space: -o-pre-wrap; /* Opera */
      white-space: pre-wrap; /* CSS3 - Text module (Candidate Recommendation) http://www.w3.org/TR/css3-text/#white-space */
      word-wrap: break-word; /* IE 5.5+ */
    }
    </style>
  </head>
  <body>
    <pre>###CONTENT###</pre>
  </body>
</html>
HTML;
					$extensionFileName = '.html';
					break;
			}
			$relativeFileName = 'typo3temp/tx_' . static::$extKey . '/' . $e->getCode() . $extensionFileName;
			$absoluteFileName = GeneralUtility::getFileAbsFileName($relativeFileName);
			$content = str_replace('###CONTENT###', $e->getMessage(), $templateContent);
			GeneralUtility::writeFile($absoluteFileName, $content);
			return '../' . $relativeFileName;
		}

		GeneralUtility::rmdir($absoluteOutputDirectory, TRUE);
		GeneralUtility::mkdir_deep($absoluteOutputDirectory . '/');

		$warningsFilename = $documentationBasePath . '/warnings.txt';
		if (is_file($warningsFilename) && filesize($warningsFilename) > 0) {
			$documentationSource = $source;
			if (!empty($locale)) {
				$documentationSource .= '/Localization.' . $locale;
			}
			$warnings = file_get_contents($warningsFilename);

			// Automatically fix Intersphinx mapping, if needed
			if ($documentationTypes & static::DOCUMENTATION_TYPE_SPHINX) {
				// Original files
				$settingsYamlFilename = static::extPath($extensionKey) . 'Documentation';
				if (!empty($locale)) {
					$settingsYamlFilename .= '/Localization.' . $locale;
				}
				$settingsYamlFilename .= '/Settings.yml';

				if (is_file($settingsYamlFilename) && is_writable($settingsYamlFilename)) {
					if (static::autofixMissingIntersphinxMapping($warningsFilename, $settingsYamlFilename)) {
						// Recompile and hope this works this time!
						static::generateDocumentation($extensionKey, $format, $force, $locale);
					}
				}
			}

			// Compatibility with Windows platform
			$warnings = str_replace(
				str_replace('/', DIRECTORY_SEPARATOR, $documentationBasePath),
				str_replace('/', DIRECTORY_SEPARATOR, $documentationSource),
				$warnings
			);
			GeneralUtility::writeFile($absoluteOutputDirectory . '/warnings.txt', $warnings);
		}

		if ($format !== 'pdf') {
			static::recursiveCopy($documentationBasePath . '/_make/build/' . $documentationFormat, $absoluteOutputDirectory);
		} else {
			// Only copy PDF output
			$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][static::$extKey]);
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
	 * Automatically fixes missing Intersphinx mapping based on sphinx-build warnings.
	 *
	 * @param string $warningsFilename
	 * @param string $settingsYamlFilename
	 * @return boolean TRUE if Settings.yml was updated, otherwise FALSE
	 */
	static public function autofixMissingIntersphinxMapping($warningsFilename, $settingsYamlFilename) {
		if (!ExtensionManagementUtility::isLoaded('restdoc')) {
			return FALSE;
		}
		$warningsLines = explode(LF, file_get_contents($warningsFilename));
		$prefixes = array();
		$intersphinxMappingUpdated = FALSE;

		foreach ($warningsLines as $warningLine) {
			if (preg_match('/ WARNING: undefined label: ([^:]+):/', $warningLine, $matches)) {
				$remoteUrl = '';
				$additionalInformation = array();
				$prefix = $matches[1];
				if (in_array($prefix, $prefixes)) {
					continue;
				}
				$reference = static::getReferenceFromIntersphinxKey($prefix, $additionalInformation);
				if ($reference !== NULL) {
					$remoteUrl = $additionalInformation['url'];
				} else {
					$reference = static::intersphinxKeyToExtensionKey($prefix);
				}
				// $remoteUrl will be "updated" by next call if $reference is an extension key
				$anchors = static::getIntersphinxReferences($reference, $locale, $remoteUrl);
				if (count($anchors) > 0 && static::addIntersphinxMapping($settingsYamlFilename, $prefix, $remoteUrl) === TRUE) {
					$intersphinxMappingUpdated = TRUE;
				}
				$prefixes[] = $prefix;
			}
		}

		return $intersphinxMappingUpdated;
	}

	/**
	 * Creates a special-crafted conf.py for JSON output when using
	 * t3sphinx as HTML theme.
	 *
	 * @param string $basePath Absolute path to the root directory of the Sphinx project
	 * @return void
	 * @see \Causal\Sphinx\Controller\ConsoleController::overrideThemeT3Sphinx()
	 * @see http://forge.typo3.org/issues/48311
	 */
	static public function overrideThemeT3Sphinx($basePath) {
		$basePath = rtrim($basePath, '/');
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

			GeneralUtility::writeFile($basePath . '/_make/conf.py', $newConfiguration);
		}
	}

	/**
	 * Recursively copy content from one directory to another.
	 *
	 * @param string $source Absolute path to the source directory
	 * @param string $target Absolute path to the target directory
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
				GeneralUtility::mkdir($target . '/' . $iterator->getSubPathName());
			} elseif (is_file($item)) {
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
	 * @param string $variable The name of the environment variable
	 * @param string $value The value of the environment variable
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
	 * Adds an Intersphinx mapping.
	 *
	 * @param string $filename Absolute filename to Settings.yml
	 * @param string $identifier Unique identifier (prefix) for Intersphinx
	 * @param string $target Base URI of the foreign Sphinx documentation
	 * @return boolean|NULL TRUE if operation succeeded (Settings.yml could be updated), otherwise FALSE (NULL if no change needed)
	 */
	static public function addIntersphinxMapping($filename, $identifier, $target) {
		$indent = '  ';

		if (!is_file($filename)) {
			$currentYear = date('Y');
			$configuration = <<<YAML
# This is the project specific Settings.yml file.
# Place Sphinx specific build information here.
# Settings given here will replace the settings of 'conf.py'.

---
conf.py:
  copyright: $currentYear
  project: No project name
  version: 1.0
  release: 1.0.0
...

YAML;
			GeneralUtility::writeFile($filename, $configuration);
		}

		$contents = file_get_contents($filename);
		// Fix line breaks if needed as we rely on Linux line breaks
		$contents = str_replace(array(CR . LF, CR), LF, $contents);
		$lines = explode(LF, $contents);
		$isDirty = FALSE;

		$startLine = 0;
		$hasIntersphinxMapping = FALSE;
		$hasDelimiter = FALSE;
		while ($startLine < count($lines)) {
			if ($lines[$startLine] === $indent . 'intersphinx_mapping:') {
				$hasIntersphinxMapping = TRUE;
				break;
			} elseif ($lines[$startLine] === '...') {
				$hasDelimiter = TRUE;
				break;
			}
			$startLine++;
		}
		if (!$hasIntersphinxMapping) {
			if ($hasDelimiter) {
				$startLines = array_slice($lines, 0, $startLine);
				$endLines = array_slice($lines, $startLine);
				$lines = array_merge(
					$startLines,
					array(
						$indent . 'intersphinx_mapping:'
					),
					$endLines
				);
			} else {
				$lines[] = $indent . 'intersphinx_mapping:';
			}
		}

		// Search if mapping is already present
		$mappingAlreadyExists = FALSE;
		$numberOfLines = count($lines);
		for ($i = $startLine + 1; $i < $numberOfLines; $i += 3) {
			if ($lines[$i] === $indent . $indent . $identifier . ':') {
				$mappingAlreadyExists = TRUE;
				break;
			}
		}
		if (!$mappingAlreadyExists) {
			// Add the mapping at the beginning of the list
			$startLines = array_slice($lines, 0, $startLine + 1);
			$endLines = array_slice($lines, $startLine + 1);
			$lines = array_merge(
				$startLines,
				array(
					$indent . $indent . $identifier . ':',
					$indent . $indent . '- ' . rtrim($target, '/') . '/',
					$indent . $indent . '- null',
				),
				$endLines
			);
			$isDirty = TRUE;
		}

		return $isDirty
			? GeneralUtility::writeFile($filename, implode(LF, $lines))
			: NULL;
	}

	/**
	 * Caches the Intersphinx mapping.
	 *
	 * @param string $filename Absolute filename to Settings.yml
	 * @return void
	 * @see http://forge.typo3.org/issues/51275
	 */
	static public function cacheIntersphinxMapping($filename) {
		$cacheDirectory = GeneralUtility::getFileAbsFileName(
			'typo3temp/tx_' . static::$extKey . '/intersphinx/'
		);

		// Clean-up caches
		$cacheFiles = GeneralUtility::getFilesInDir($cacheDirectory);
		foreach ($cacheFiles as $cacheFile) {
			if ($GLOBALS['EXEC_TIME'] - filemtime($cacheDirectory . $cacheFile) > 28800) {	// 8 hours of cache
				@unlink($cacheDirectory . $cacheFile);
			}
		}

		$indent = '  ';
		$contents = file_get_contents($filename);
		// Fix line breaks if needed as we rely on Linux line breaks
		$contents = str_replace(array(CR . LF, CR), LF, $contents);
		$lines = explode(LF, $contents);
		$isDirty = FALSE;

		$startLine = 0;
		$hasIntersphinxMapping = FALSE;
		while ($startLine < count($lines)) {
			if ($lines[$startLine] === $indent . 'intersphinx_mapping:') {
				$hasIntersphinxMapping = TRUE;
				break;
			}
			$startLine++;
		}

		if ($hasIntersphinxMapping) {
			$numberOfLines = count($lines);
			for ($i = $startLine + 1; $i < $numberOfLines; $i += 3) {
				if (!preg_match('/^' . $indent . $indent . '(.+):/', $lines[$i], $matches)) {
					break;
				}
				$prefix = $matches[1];
				$remoteUrl = trim(substr($lines[$i + 1], strlen($indent . $indent . '- ')));
				$remoteUrl = rtrim($remoteUrl, '/') . '/objects.inv';
				$cacheFile = $cacheDirectory . $prefix . '-' . md5($remoteUrl) . '-objects.inv';
				if (!is_file($cacheFile)) {
					$objectsInv = static::getUrl($remoteUrl);
					if ($objectsInv) {
						GeneralUtility::mkdir_deep(PathUtility::dirname($cacheFile) . '/');
						GeneralUtility::writeFile($cacheFile, $objectsInv);
					}
				}
				$lines[$i + 2] = $indent . $indent . '- ' . $cacheFile;
				$isDirty = TRUE;
			}
		}

		if ($isDirty) {
			GeneralUtility::writeFile($filename, implode(LF, $lines));
		}
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
	 * @param string $filename Absolute filename to Settings.yml
	 * @return string Python instruction set
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
			if (preg_match('/^(\s+)([^:]+):\s*(.*)$/', $lines[$i], $matches)) {
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
					case 'html_theme_options':
						$pythonLine = $matches[2] . ' = {' . LF;
						if (preg_match('/^(\s+)/', $lines[$i + 1], $matches)) {
							$indent = $matches[1];
							$firstLine = TRUE;
							while (preg_match('/^' . $indent . '([^:]+):\s*(.*)$/', $lines[++$i], $matches)) {
								if (!$firstLine) {
									$pythonLine .= ',' . LF;
								}
								$pythonLine .= sprintf('\'%s\': ', $matches[1]);
								if ($matches[2] === 'null') {
									$pythonLine .= 'None';
								} elseif (GeneralUtility::inList('true,false', $matches[2])) {
									$pythonLine .= ucfirst($matches[2]);
								} elseif (\TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($matches[2])) {
									$pythonLine .= intval($matches[2]);
								} else {
									$pythonLine .= sprintf('\'%s\'', addcslashes($matches[2], "\\'"));
								}
								$firstLine = FALSE;
							}
						}
						$pythonLine .= LF . '}';
						$i--;
					break;
					case 'extensions':
						$pythonLine = 'extensions = [';
						if (preg_match('/^(\s+)/', $lines[$i + 1], $matches)) {
							$indent = $matches[1];
							$firstItem = TRUE;
							while (preg_match('/^' . $indent . '- (.+)/', $lines[++$i], $matches)) {
								if (GeneralUtility::isFirstPartOfStr($matches[1], 't3sphinx.')) {
									// Extension t3sphinx is not compatible with JSON output
									continue;
								}

								if (!$firstItem) {
									$pythonLine .= ', ';
								}
								$pythonLine .= sprintf('\'%s\'', addcslashes($matches[1], "\\'"));
								$firstItem = FALSE;
							}
							$i--;
						}
						$pythonLine .= ']';
					break;
					case 'extlinks':
					case 'intersphinx_mapping':
						$pythonLine = $matches[2] . ' = {' . LF;
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
										$pythonLine .= sprintf('\'%s\'', trim(trim($matches[1]), '\''));
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
			$i++;
		}

		return $pythonConfiguration;
	}

	/**
	 * Reads the file or url $url and returns the content
	 * If you are having trouble with proxies when reading URLs you can configure your way out of that with settings
	 * like $GLOBALS['TYPO3_CONF_VARS']['SYS']['curlUse'] etc.
	 *
	 * @param string $url File/URL to read
	 * @return mixed The content from the resource given as input. FALSE if an error has occurred.
	 */
	static public function getUrl($url) {
		// Known problems when using GeneralUtility::getUrl() with https:// resources
		// E.g., https://bitbucket.org/xperseguers/sphinx-contrib/downloads
		// where text/html is expected
		if (!GeneralUtility::isFirstPartOfStr($url, 'https://')) {
			return GeneralUtility::getUrl($url);
		}

		/** @var $http \TYPO3\CMS\Core\Http\HttpRequest */
		$http = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Http\\HttpRequest', $url);
		try {
			return $http->send()->getBody();
		} catch (\Exception $e) {
			return FALSE;
		}
	}

	/**
	 * Reads the file or url $url and returns the content and cache result for quicker
	 * consecutive access.
	 *
	 * @param string $url File/URL to read
	 * @param int $cacheLifetime Lifetime of cache, in seconds
	 * @return mixed The content from the resource given as input. FALSE in an error has occured.
	 */
	static public function getUrlWithCache($url, $cacheLifetime = 86400) {
		$extension = '';
		if (($pos = strrpos($url, '.')) !== FALSE) {
			$extension = strtolower(substr($url, $pos + 1));
		}
		if ($extension === '' || strlen($extension) > 5) {
			$extension = 'html';
		}
		$cacheFilename = static::getTemporaryPath() . static::$extKey . '.' . md5($url) . '.' . $extension;
		if (!file_exists($cacheFilename)
			|| $GLOBALS['EXEC_TIME'] - filemtime($cacheFilename) > $cacheLifetime
			|| filesize($cacheFilename) == 0) {

			$content = static::getUrl($url);
			if ($content) {
				GeneralUtility::writeFile($cacheFilename, $content);
			}
		} else {
			$content = file_get_contents($cacheFilename);
		}
		return $content;
	}

	/**
	 * Checks if a given URL is valid.
	 *
	 * @param string $url URL to check
	 * @return bool
	 */
	static public function checkUrl($url) {
		/** @var $http \TYPO3\CMS\Core\Http\HttpRequest */
		$http = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Http\\HttpRequest', $url, \TYPO3\CMS\Core\Http\HttpRequest::METHOD_HEAD);
		try {
			return count($http->send()->getHeader()) > 0;
		} catch (\Exception $e) {
			return FALSE;
		}
	}

	/**
	 * Returns the path to a given extension, relative to site root.
	 *
	 * @param string $extensionKey
	 * @return string|NULL
	 */
	static public function extRelPath($extensionKey) {
		static $availableAndInstalledExtensions = NULL;

		if (isset($GLOBALS['TYPO3_LOADED_EXT'][$extensionKey])) {
			return $GLOBALS['TYPO3_LOADED_EXT'][$extensionKey]['siteRelPath'];
		}
		if ($availableAndInstalledExtensions === NULL) {
			try {
				$availableAndInstalledExtensions = static::getListUtility()->getAvailableAndInstalledExtensionsWithAdditionalInformation();
			} catch (\Exception $e) {
				$availableAndInstalledExtensions = array();
			}
		}
		if (isset($availableAndInstalledExtensions[$extensionKey])) {
			return rtrim($availableAndInstalledExtensions[$extensionKey]['siteRelPath'], '/') . '/';
		} else {
			return NULL;
		}
	}

	/**
	 * Returns the absolute path to a given extension.
	 *
	 * @param string $extensionKey
	 * @return string|NULL
	 */
	static public function extPath($extensionKey) {
		$relPath = static::extRelPath($extensionKey);
		if ($relPath !== NULL) {
			return PATH_site . $relPath;
		} else {
			return NULL;
		}
	}

	/**
	 * @return \TYPO3\CMS\Extensionmanager\Utility\ListUtility
	 * @throws \InvalidArgumentException
	 * @throws \TYPO3\CMS\Extbase\Object\Exception
	 * @throws \TYPO3\CMS\Extbase\Object\Exception\CannotBuildObjectException
	 */
	static protected function getListUtility() {
		if (static::$listUtility === NULL) {
			/** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
			$objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
			static::$listUtility = $objectManager->get('TYPO3\\CMS\\Extensionmanager\\Utility\\ListUtility');
		}
		return static::$listUtility;
	}

	/**
	 * Returns a link to the Extension Manager (EM) for an optional given action.
	 *
	 * @param string $extensionKey
	 * @param string $controller
	 * @param string $action
	 * @param array $additionalUrlParameters
	 * @return string
	 */
	static public function getExtensionManagerLink($extensionKey = '', $controller = '', $action = '', array $additionalUrlParameters = array()) {
		$namespace = 'tx_extensionmanager_tools_extensionmanagerextensionmanager';
		$moduleName = 'tools_ExtensionmanagerExtensionmanager';
		$urlParameters = $additionalUrlParameters;

		if (!empty($extensionKey)) {
			$urlParameters[$namespace . '[extension][key]'] = $extensionKey;
			$urlParameters[$namespace . '[extensionKey]'] = $extensionKey;
		}
		if (!empty($controller) && !empty($action)) {
			$urlParameters[$namespace . '[controller]'] = $controller;
			$urlParameters[$namespace . '[action]'] = $action;
		}

		$extensionManagerUri = \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleUrl($moduleName, $urlParameters, FALSE, TRUE);
		return $extensionManagerUri;
	}

	/**
	 * Returns the path to the website's temporary directory.
	 *
	 * @return string Absolute path to typo3temp/
	 */
	static public function getTemporaryPath() {
		$temporaryPath = GeneralUtility::getFileAbsFileName('typo3temp/');
		// Compatibility with Windows platform
		$temporaryPath = str_replace('/', DIRECTORY_SEPARATOR, $temporaryPath);

		return $temporaryPath;
	}

}
