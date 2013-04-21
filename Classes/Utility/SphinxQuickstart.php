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

/**
 * SphinxQuickstart Wrapper.
 *
 * @category    Utility
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @version     SVN: $Id$
 */
class Tx_Sphinx_Utility_SphinxQuickstart {

	/** @var string */
	protected static $extKey = 'sphinx';

	/**
	 * Creates an empty Sphinx project.
	 *
	 * @param string $pathRoot
	 * @param string $projectName
	 * @param string $author
	 * @param boolean $separateSourceBuild
	 * @return boolean
	 */
	public function createProject($pathRoot, $projectName, $author, $separateSourceBuild = FALSE) {
		$projectName = str_replace("'", ' ', $projectName);
		$author = str_replace("'", ' ', $author);

		// Inside the root directory, two more directories will be created; "_templates"
		// for custom HTML templates and "_static" for custom stylesheets and other static
		// files. You can enter another prefix (such as ".") to replace the underscore.
		$namePrefixTemplatesStatic = '_';

		// Sphinx has the notion of a "version" and a "release" for the
		// software. Each version can have multiple releases. For example, for
		// Python the version is something like 2.5 or 3.0, while the release is
		// something like 2.5.1 or 3.0a1.  If you don't need this dual structure,
		// just set both to the same value.
		$version = '1.0';
		$release = '1.0.0';

		// The file name suffix for source files. Commonly, this is either ".txt"
		// or ".rst".  Only files with this suffix are considered documents.
		$sourceFileSuffix = '.rst';

		// One document is special in that it is considered the top node of the
		// "contents tree", that is, it is the root of the hierarchical structure
		// of the documents. Normally, this is "index", but if your "index"
		// document is a custom template, you can also set this to another filename.
		$masterDocument = 'index';

		$pathRoot = rtrim($pathRoot, '/') . '/';
		\TYPO3\CMS\Core\Utility\GeneralUtility::mkdir_deep($pathRoot);

		if ($separateSourceBuild) {
			$directories = array(
				'source/' . $namePrefixTemplatesStatic . 'static/',
				'source/' . $namePrefixTemplatesStatic . 'templates/',
				'build/',
			);
			$files = array(
				'conf.py' => 'source/conf.py',
				'master'  => 'source/' . $masterDocument . $sourceFileSuffix,
			);
			$excludePattern = '';
		} else {
			$directories = array(
				$namePrefixTemplatesStatic . 'static/',
				$namePrefixTemplatesStatic . 'templates/',
				'_build/',
			);
			$files = array(
				'conf.py' => 'conf.py',
				'master'  => $masterDocument . $sourceFileSuffix,
			);
			$excludePattern = '_build';
		}
		foreach ($directories as $directory) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::mkdir_deep($pathRoot . $directory);
		}

		/** @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $contentObj */
		$contentObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer');

		$markers = array(
			'PROJECT'            => str_replace(' ', '', $projectName),
			'PROJECT_NAME'       => $projectName,
			'AUTHOR'             => $author,
			'VERSION'            => $version,
			'RELEASE'            => $release,
			'CURRENT_DATE'       => date('r'),
			'YEAR'               => date('Y'),
			'MASTER_DOCUMENT'    => $masterDocument,
			'PATH_TEMPLATES'     => $namePrefixTemplatesStatic . 'templates',
			'PATH_STATIC'        => $namePrefixTemplatesStatic . 'static',
			'SOURCE_FILE_SUFFIX' => $sourceFileSuffix,
			'EXCLUDE_PATTERN'    => $excludePattern,
		);

		$templateMaster = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath(self::$extKey) . 'Resources/Private/Templates/BlankProject/MasterDocument.rst.tmpl';
		$template = file_get_contents($templateMaster);
		$master = $contentObj->substituteMarkerArray($template, $markers, '###|###');
		\TYPO3\CMS\Core\Utility\GeneralUtility::writeFile($pathRoot . $files['master'], $master);

		$templateConfPy = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath(self::$extKey) . 'Resources/Private/Templates/BlankProject/conf.py.tmpl';
		$template = file_get_contents($templateConfPy);
		$conf = $contentObj->substituteMarkerArray($template, $markers, '###|###');
		\TYPO3\CMS\Core\Utility\GeneralUtility::writeFile($pathRoot . $files['conf.py'], $conf);

		return TRUE;
	}

}

?>