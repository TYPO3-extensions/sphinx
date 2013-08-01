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

/**
 * SphinxQuickstart Wrapper.
 *
 * @category    Utility
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class SphinxQuickstart {

	/** @var string */
	protected static $extKey = 'sphinx';

	/**
	 * Creates an empty Sphinx project.
	 *
	 * @param string $pathRoot
	 * @param string $projectName
	 * @param string $author
	 * @param boolean $separateSourceBuild
	 * @param string $template
	 * @param string $version
	 * @param string $release
	 * @param string $project Name for LaTeX, man pages, ... output, defaults to $projectName
	 * @return boolean
	 */
	static public function createProject($pathRoot, $projectName, $author, $separateSourceBuild = FALSE, $template = 'BlankProject', $version = '1.0', $release = '1.0.0', $project = '') {
		$projectName = str_replace("'", ' ', $projectName);
		$author = str_replace("'", ' ', $author);
		if (empty($project)) {
			$project = $projectName;
		}
		$project = str_replace(array("'", ' '), '', $project);

		// Inside the root directory, two more directories will be created; "_templates"
		// for custom HTML templates and "_static" for custom stylesheets and other static
		// files. You can enter another prefix (such as ".") to replace the underscore.
		$namePrefixTemplatesStatic = '_';

		// Sphinx has the notion of a "version" and a "release" for the
		// software. Each version can have multiple releases. For example, for
		// Python the version is something like 2.5 or 3.0, while the release is
		// something like 2.5.1 or 3.0a1.  If you don't need this dual structure,
		// just set both to the same value.
		//$version = '1.0';
		//$release = '1.0.0';

		// The file name suffix for source files. Commonly, this is either ".txt"
		// or ".rst".  Only files with this suffix are considered documents.
		$sourceFileSuffix = '.rst';

		// One document is special in that it is considered the top node of the
		// "contents tree", that is, it is the root of the hierarchical structure
		// of the documents. Normally, this is "index", but if your "index"
		// document is a custom template, you can also set this to another filename.
		$masterDocument = 'index';

		$pathRoot = rtrim($pathRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		\TYPO3\CMS\Core\Utility\GeneralUtility::mkdir_deep($pathRoot);

		$isTypo3Documentation = is_dir(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath(self::$extKey) . 'Resources/Private/Templates/Projects/' . $template . '/_make');

		if ($isTypo3Documentation) {
			$separateSourceBuild = FALSE;
			$masterDocument = 'Index';
			$buildDirectory = 'build';
			$directories = array(
				$namePrefixTemplatesStatic . 'static' . DIRECTORY_SEPARATOR,
				$namePrefixTemplatesStatic . 'templates' . DIRECTORY_SEPARATOR,
				'_make/build/',
			);
			$excludePattern = '_make';
		} elseif ($separateSourceBuild) {
			$buildDirectory = 'build';
			$directories = array(
				'source/' . $namePrefixTemplatesStatic . 'static' . DIRECTORY_SEPARATOR,
				'source/' . $namePrefixTemplatesStatic . 'templates' . DIRECTORY_SEPARATOR,
				'build/',
			);
			$excludePattern = '';
		} else {
			$buildDirectory = '_build';
			$directories = array(
				$namePrefixTemplatesStatic . 'static' . DIRECTORY_SEPARATOR,
				$namePrefixTemplatesStatic . 'templates' . DIRECTORY_SEPARATOR,
				'_build/',
			);
			$excludePattern = '_build';
		}
		foreach ($directories as $directory) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::mkdir_deep($pathRoot . $directory);
		}

		$binDirectory = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath(self::$extKey) . 'Resources/Private/sphinx/bin/';

		// Compatibility with Windows platform
		$binDirectory = str_replace('/', DIRECTORY_SEPARATOR, $binDirectory);

		$markers = array(
			'PROJECT'            => $project,
			'PROJECT_NAME'       => $projectName,
			'AUTHOR'             => $author,
			'VERSION'            => $version,
			'RELEASE'            => $release,
			'CURRENT_DATE'       => date('r'),
			'YEAR'               => date('Y'),
			'MASTER_DOCUMENT'    => $masterDocument,
			'PATH_TEMPLATES'     => ($isTypo3Documentation ? '../' : '') . $namePrefixTemplatesStatic . 'templates',
			'PATH_STATIC'        => ($isTypo3Documentation ? '../' : '') . $namePrefixTemplatesStatic . 'static',
			'SOURCE_FILE_SUFFIX' => $sourceFileSuffix,
			'EXCLUDE_PATTERN'    => $excludePattern,
			'BUILD_DIRECTORY'    => $buildDirectory,
			'BIN_DIRECTORY'      => $binDirectory,
		);

		$config = array(
			'template'         => $template,
			'path'             => $separateSourceBuild ? $pathRoot . 'source' . DIRECTORY_SEPARATOR : $pathRoot,
			'masterDocument'   => $masterDocument,
			'sourceFileSuffix' => $sourceFileSuffix,
			'markers'          => $markers,
		);

		return self::createFromTemplate($config);
	}

	/**
	 * Instantiates a documentation template.
	 *
	 * @param array $config
	 * @return boolean
	 * @throws \RuntimeException
	 */
	protected static function createFromTemplate(array $config) {
		/** @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $contentObj */
		$contentObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer');

		// Recursively instantiate template files
		$source = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath(self::$extKey) . 'Resources/Private/Templates/Projects/' . $config['template'] . '/';
		if (!is_dir($source)) {
			throw new \RuntimeException('Template directory was not found: ' . $source, 1367044890);
		}
		/** @var \RecursiveDirectoryIterator $iterator */
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($source,
			\RecursiveDirectoryIterator::SKIP_DOTS),
			\RecursiveIteratorIterator::SELF_FIRST
		);
		foreach ($iterator as $item) {
			/** @var \splFileInfo $item */
			if ($item->isDir()) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::mkdir($config['path'] . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
			} else {
				if (substr($item, -5) === '.tmpl') {
					$targetSubPathName = substr($iterator->getSubPathName(), 0, -5);
					if (substr($targetSubPathName, -18) === 'MasterDocument.rst') {
						$targetSubPathName = substr($targetSubPathName, 0, -18) . $config['masterDocument'] . $config['sourceFileSuffix'];
					}
					$contents = file_get_contents($item);
					$contents = $contentObj->substituteMarkerArray($contents, $config['markers'], '###|###');
					\TYPO3\CMS\Core\Utility\GeneralUtility::writeFile($config['path'] . DIRECTORY_SEPARATOR . $targetSubPathName, $contents);
				} elseif (substr($item, -4) === '.rst') {
					$targetSubPathName = substr($iterator->getSubPathName(), 0, -4) . $config['sourceFileSuffix'];
					copy($item, $config['path'] . DIRECTORY_SEPARATOR . $targetSubPathName);
				} else {
					copy($item, $config['path'] . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
				}
			}
		}

		return TRUE;
	}

}

?>