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
 * SphinxBuilder Wrapper.
 *
 * @category    Utility
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @version     SVN: $Id$
 */
class Tx_Sphinx_Utility_SphinxBuilder {

	/** @var string */
	protected static $extKey = 'sphinx';

	/**
	 * Builds a Sphinx project as HTML.
	 *
	 * @param string $basePath
	 * @param string $sourceDirectory
	 * @param string $buildDirectory
	 * @return string Output of the build process (if succeeded)
	 * @throws RuntimeException if build process failed
	 */
	public static function buildHtml($basePath, $sourceDirectory = '.', $buildDirectory = '_build') {
		$sphinxBuilder = self::getSphinxBuilder();

		$basePath = rtrim($basePath, '/') . '/';
		$sourceDirectory = rtrim($sourceDirectory);
		$buildDirectory = rtrim($buildDirectory);

		if (!(is_dir($basePath) && is_file($basePath . $sourceDirectory . '/conf.py'))) {
			throw new RuntimeException('No Sphinx project found in ' . $basePath . $sourceDirectory . '/', 1366210585);
		}

		$cmd = 'cd ' . escapeshellarg($basePath) . ' && ' .
			$sphinxBuilder . ' -b html' .									// output format
				' -d ' . escapeshellarg($buildDirectory . '/doctrees') .	// references
				' ' . escapeshellarg($sourceDirectory) .					// source directory
				' ' . escapeshellarg($buildDirectory . '/html') .			// build directory
				' 2>&1';													// redirect errors to STDOUT

		$output = array();
		t3lib_utility_Command::exec($cmd, $output, $ret);
		$output = implode(LF, $output);
		if ($ret !== 0) {
			throw new RuntimeException('Cannot build Sphinx project:' . LF . $output, 1366212039);
		}
		return $output;
	}

	/**
	 * Returns the SphinxBuilder command.
	 *
	 * @return string
	 * @throws RuntimeException
	 */
	protected static function getSphinxBuilder() {
		$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][self::$extKey]);
		$sphinxPath = t3lib_extMgm::extPath(self::$extKey) . 'Resources/Private/sphinx/' . $configuration['version'] . '/';
		$sphinxBuilder = $sphinxPath . 'bin/sphinx-build';

		if (empty($configuration['version']) || !is_executable($sphinxBuilder)) {
			throw new RuntimeException('Sphinx is not available', 1366210198);
		}

		$cmd = 'export PYTHONPATH=' . escapeshellarg($sphinxPath . 'lib/python') . ' && ' . $sphinxBuilder;
		return $cmd;
	}

}

?>