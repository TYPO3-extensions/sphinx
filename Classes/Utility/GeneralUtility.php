<?php
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

namespace Causal\Sphinx\Utility;

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

	/** @var string */
	protected static $extKey = 'sphinx';

	/**
	 * Returns meta-data for a given extension.
	 *
	 * @param string $extensionKey
	 * @return array
	 */
	public static function getExtensionMetaData($extensionKey) {
		$_EXTKEY = $extensionKey;
		$EM_CONF = array();
		$extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extensionKey);
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
	 * Generates the documentation for a given extension.
	 *
	 * @param string $extensionKey
	 * @param string $format
	 * @param boolean $force
	 * @return string
	 */
	public static function generateDocumentation($extensionKey, $format = 'html', $force = FALSE) {
		switch ($format) {
			case 'json':
				$documentationType = 'json';
				$masterDocument = 'Index.fjson';
				break;
			case 'html':
			default:
				$documentationType = 'html';
				$masterDocument = 'Index.html';
				break;
		}

		$outputDirectory = PATH_site . 'typo3conf/Documentation/' . $extensionKey . '/' . $documentationType;
		if (!$force && is_file($outputDirectory . '/' . $masterDocument)) {
			// Do not render the documentation again
			$documentationUrl = '../' . substr($outputDirectory, strlen(PATH_site)) . '/' . $masterDocument;
			return $documentationUrl;
		}

		$metadata = \Causal\Sphinx\Utility\GeneralUtility::getExtensionMetaData($extensionKey);
		$basePath = PATH_site . 'typo3temp/tx_' . self::$extKey . '/' . $extensionKey;
		\TYPO3\CMS\Core\Utility\GeneralUtility::rmdir($basePath, TRUE);
		\Causal\Sphinx\Utility\SphinxQuickstart::createProject(
			$basePath,
			$extensionKey,
			$metadata['author'],
			FALSE,
			'TYPO3DocEmptyProject',
			$metadata['version'],
			$metadata['release']
		);

		// Recursively instantiate template files
		$source = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extensionKey) . 'Documentation';
		if (!is_dir($source)) {
			$filename = 'typo3temp/tx_' . self::$extKey . '/1369679343.log';
			$content = 'ERROR 1369679343: Documentation directory was not found: ' . $source;
			\TYPO3\CMS\Core\Utility\GeneralUtility::writeFile(PATH_site . $filename, $content);
			return '../' . $filename;
		}
		self::recursiveCopy($source, $basePath);

		try {
			if ($format === 'json') {
				\Causal\Sphinx\Utility\SphinxBuilder::buildJson($basePath, '.', '_make/build', '_make/conf.py');
			} else {
				\Causal\Sphinx\Utility\SphinxBuilder::buildHtml($basePath, '.', '_make/build', '_make/conf.py');
			}
		} catch (\RuntimeException $e) {
			$filename = 'typo3temp/tx_' . self::$extKey . '/' . $e->getCode() . '.log';
			$content = $e->getMessage();
			\TYPO3\CMS\Core\Utility\GeneralUtility::writeFile(PATH_site . $filename, $content);
			return '../' . $filename;
		}

		\TYPO3\CMS\Core\Utility\GeneralUtility::rmdir($outputDirectory, TRUE);
		\TYPO3\CMS\Core\Utility\GeneralUtility::mkdir_deep($outputDirectory . '/');
		self::recursiveCopy($basePath . '/_make/build/' . $documentationType, $outputDirectory);

		$documentationUrl = '../' . substr($outputDirectory, strlen(PATH_site)) . '/' . $masterDocument;
		return $documentationUrl;
	}

	/**
	 * Recursively copy content from one directory to another.
	 *
	 * @param string $source
	 * @param string $target
	 * @return void
	 */
	protected static function recursiveCopy($source, $target) {
		$target = rtrim($target, '/');
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($source,
				\RecursiveDirectoryIterator::SKIP_DOTS),
			\RecursiveIteratorIterator::SELF_FIRST
		);
		foreach ($iterator as $item) {
			if ($item->isDir()) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::mkdir($target . '/' . $iterator->getSubPathName());
			} else {
				copy($item, $target . '/' . $iterator->getSubPathName());
			}
		}
	}

}

?>