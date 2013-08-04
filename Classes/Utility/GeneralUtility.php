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
	 * @return string The documentation URL
	 */
	static public function generateDocumentation($extensionKey, $format = 'html', $force = FALSE) {
		$documentationType = self::getDocumentationType($extensionKey);
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

		$outputDirectory = PATH_site . 'typo3conf/Documentation/typo3cms.extensions.' . $extensionKey . '/default/' . $documentationFormat;
		if (!$force && is_file($outputDirectory . '/' . $masterDocument)) {
			// Do not render the documentation again
			$documentationUrl = '../' . substr($outputDirectory, strlen(PATH_site)) . '/' . $masterDocument;
			return $documentationUrl;
		}

		$metadata = GeneralUtility::getExtensionMetaData($extensionKey);
		$basePath = PATH_site . 'typo3temp/tx_' . self::$extKey . '/' . $extensionKey;
		\TYPO3\CMS\Core\Utility\GeneralUtility::rmdir($basePath, TRUE);
		SphinxQuickstart::createProject(
			$basePath,
			$metadata['title'],
			$metadata['author'],
			FALSE,
			'TYPO3DocEmptyProject',
			$metadata['version'],
			$metadata['release'],
			$extensionKey
		);

		if ($format === 'json') {
			self::overrideThemeT3Sphinx($basePath);
		}

		// Recursively instantiate template files
		switch ($documentationType) {
			case self::DOCUMENTATION_TYPE_SPHINX:
				$source = ExtensionManagementUtility::extPath($extensionKey) . 'Documentation';
				self::recursiveCopy($source, $basePath);
				break;
			case self::DOCUMENTATION_TYPE_README:
				$source = ExtensionManagementUtility::extPath($extensionKey) . 'README.rst';
				copy($source, $basePath . '/Index.rst');
		}

		try {
			if ($format === 'json') {
				SphinxBuilder::buildJson($basePath, '.', '_make/build', '_make/conf.py');
			} elseif ($format === 'pdf') {
				SphinxBuilder::buildPdf($basePath, '.', '_make/build', '_make/conf.py');
			} else {
				SphinxBuilder::buildHtml($basePath, '.', '_make/build', '_make/conf.py');
			}
		} catch (\RuntimeException $e) {
			$filename = 'typo3temp/tx_' . self::$extKey . '/' . $e->getCode() . '.log';
			$content = $e->getMessage();
			\TYPO3\CMS\Core\Utility\GeneralUtility::writeFile(PATH_site . $filename, $content);
			return '../' . $filename;
		}

		\TYPO3\CMS\Core\Utility\GeneralUtility::rmdir($outputDirectory, TRUE);
		\TYPO3\CMS\Core\Utility\GeneralUtility::mkdir_deep($outputDirectory . '/');
		if ($format !== 'pdf') {
			self::recursiveCopy($basePath . '/_make/build/' . $documentationFormat, $outputDirectory);
		} else {
			// Only copy PDF output
			$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][self::$extKey]);
			switch ($configuration['pdf_builder']) {
				case 'pdflatex':
					copy($basePath . '/_make/build/latex/' . $extensionKey . '.pdf', $outputDirectory . '/' . $extensionKey . '.pdf');
					break;
				case 'rst2pdf':
					copy($basePath . '/_make/build/pdf/' . $extensionKey . '.pdf', $outputDirectory . '/' . $extensionKey . '.pdf');
					break;
			}
		}

		$documentationUrl = '../' . substr($outputDirectory, strlen(PATH_site)) . '/' . $masterDocument;
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
	static protected function overrideThemeT3Sphinx($basePath) {
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

}

?>