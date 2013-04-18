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
	 * Returns the version of Sphinx used for building documentation.
	 *
	 * @return string
	 */
	public static function getSphinxVersion() {
		$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][self::$extKey]);
		return $configuration['version'];
	}

	/**
	 * Builds a Sphinx project as HTML.
	 *
	 * @param string $basePath
	 * @param string $sourceDirectory
	 * @param string $buildDirectory
	 * @return string Output of the build process (if succeeded)
	 * @throws \RuntimeException if build process failed
	 */
	public static function buildHtml($basePath, $sourceDirectory = '.', $buildDirectory = '_build') {
		$sphinxBuilder = self::getSphinxBuilder();

		$basePath = rtrim($basePath, '/') . '/';
		$sourceDirectory = rtrim($sourceDirectory);
		$buildDirectory = rtrim($buildDirectory);

		if (!(is_dir($basePath) && is_file($basePath . $sourceDirectory . '/conf.py'))) {
			throw new \RuntimeException('No Sphinx project found in ' . $basePath . $sourceDirectory . '/', 1366210585);
		}

		$cmd = 'cd ' . escapeshellarg($basePath) . ' && ' .
			$sphinxBuilder . ' -b html' .									// output format
				' -d ' . escapeshellarg($buildDirectory . '/doctrees') .	// references
				' ' . escapeshellarg($sourceDirectory) .					// source directory
				' ' . escapeshellarg($buildDirectory . '/html') .			// build directory
				' 2>&1';													// redirect errors to STDOUT

		$output = array();
		\TYPO3\CMS\Core\Utility\CommandUtility::exec($cmd, $output, $ret);
		$output = implode(LF, $output);
		if ($ret !== 0) {
			throw new \RuntimeException('Cannot build Sphinx project:' . LF . $output, 1366212039);
		}

		$output .= LF;
		$output .= 'Build finished. The HTML pages are in ' . $buildDirectory . '/html.';

		return $output;
	}

	/**
	 * Builds a Sphinx project as JSON.
	 *
	 * @param string $basePath
	 * @param string $sourceDirectory
	 * @param string $buildDirectory
	 * @return string Output of the build process (if succeeded)
	 * @throws \RuntimeException if build process failed
	 */
	public static function buildJson($basePath, $sourceDirectory = '.', $buildDirectory = '_build') {
		$sphinxBuilder = self::getSphinxBuilder();

		$basePath = rtrim($basePath, '/') . '/';
		$sourceDirectory = rtrim($sourceDirectory);
		$buildDirectory = rtrim($buildDirectory);

		if (!(is_dir($basePath) && is_file($basePath . $sourceDirectory . '/conf.py'))) {
			throw new \RuntimeException('No Sphinx project found in ' . $basePath . $sourceDirectory . '/', 1366210585);
		}

		$cmd = 'cd ' . escapeshellarg($basePath) . ' && ' .
			$sphinxBuilder . ' -b json' .								// output format
			' -d ' . escapeshellarg($buildDirectory . '/doctrees') .	// references
			' ' . escapeshellarg($sourceDirectory) .					// source directory
			' ' . escapeshellarg($buildDirectory . '/json') .			// build directory
			' 2>&1';													// redirect errors to STDOUT

		$output = array();
		\TYPO3\CMS\Core\Utility\CommandUtility::exec($cmd, $output, $ret);
		$output = implode(LF, $output);
		if ($ret !== 0) {
			throw new \RuntimeException('Cannot build Sphinx project:' . LF . $output, 1366212039);
		}

		$output .= LF;
		$output .= 'Build finished; now you can process the JSON files.';

		return $output;
	}

	/**
	 * Checks links of a Sphinx project.
	 *
	 * @param string $basePath
	 * @param string $sourceDirectory
	 * @param string $buildDirectory
	 * @return string Output of the check process (if succeeded)
	 * @throws \RuntimeException if check process failed
	 */
	public static function checkLinks($basePath, $sourceDirectory = '.', $buildDirectory = '_build') {
		$sphinxBuilder = self::getSphinxBuilder();

		$basePath = rtrim($basePath, '/') . '/';
		$sourceDirectory = rtrim($sourceDirectory);
		$buildDirectory = rtrim($buildDirectory);

		if (!(is_dir($basePath) && is_file($basePath . $sourceDirectory . '/conf.py'))) {
			throw new \RuntimeException('No Sphinx project found in ' . $basePath . $sourceDirectory . '/', 1366210585);
		}

		$cmd = 'cd ' . escapeshellarg($basePath) . ' && ' .
			$sphinxBuilder . ' -b linkcheck' .							// output format
			' -d ' . escapeshellarg($buildDirectory . '/doctrees') .	// references
			' ' . escapeshellarg($sourceDirectory) .					// source directory
			' ' . escapeshellarg($buildDirectory . '/linkcheck') .		// build directory
			' 2>&1';													// redirect errors to STDOUT

		$output = array();
		\TYPO3\CMS\Core\Utility\CommandUtility::exec($cmd, $output, $ret);
		$output = implode(LF, $output);
		if ($ret !== 0) {
			throw new \RuntimeException('Cannot build Sphinx project:' . LF . $output, 1366212039);
		}

		$output .= LF;
		$output .= 'Link check complete; look for any errors in the above output ';
		$output .= 'or in ' . $buildDirectory . '/linkcheck/output.txt.';

		return $output;
	}

	/**
	 * Returns the SphinxBuilder command.
	 *
	 * @return string
	 * @throws \RuntimeException
	 */
	protected static function getSphinxBuilder() {
		$sphinxVersion = self::getSphinxVersion();
		$sphinxPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath(self::$extKey) . 'Resources/Private/sphinx/' . $sphinxVersion . '/';
		$sphinxBuilder = $sphinxPath . 'bin/sphinx-build';

		if (empty($sphinxVersion)) {
			throw new \RuntimeException('Sphinx is not configured. Please use Extension Manager.', 1366210198);
		} elseif (!is_executable($sphinxBuilder)) {
			throw new \RuntimeException('Sphinx ' . $sphinxVersion . ' cannot be executed.', 1366280021);
		}

		$cmd = 'export PYTHONPATH=' . escapeshellarg($sphinxPath . 'lib/python') . ' && ' . $sphinxBuilder;
		return $cmd;
	}

}

?>