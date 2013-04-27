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
class SphinxBuilder {

	/** @var string */
	protected static $extKey = 'sphinx';

	/** @var boolean */
	public static $htmlConsole = TRUE;

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
	 * @param string $conf
	 * @return string Output of the build process (if succeeded)
	 * @throws \RuntimeException if build process failed
	 */
	public static function buildHtml($basePath, $sourceDirectory = '.', $buildDirectory = '_build', $conf = '') {
		$sphinxBuilder = self::getSphinxBuilder();

		if (empty($conf)) {
			$conf = '.' . DIRECTORY_SEPARATOR . 'conf.py';
		}
		$basePath = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		$sourceDirectory = rtrim($sourceDirectory);
		$buildDirectory = rtrim($buildDirectory);

		if (!(is_dir($basePath) && (is_file($conf) || is_file($basePath . $conf)))) {
			throw new \RuntimeException('No Sphinx project found in ' . $basePath . $sourceDirectory . DIRECTORY_SEPARATOR, 1366210585);
		}

		$referencesPath = $buildDirectory . DIRECTORY_SEPARATOR . 'doctrees';
		$buildPath = $buildDirectory . DIRECTORY_SEPARATOR . 'html';
		$cmd = 'cd ' . escapeshellarg($basePath) . ' && ' .
			$sphinxBuilder . ' -b html' .					// output format
			' -c ' . escapeshellarg(substr($conf, 0, -7)) .	// directory with configuration file conf.py
			' -d ' . escapeshellarg($referencesPath) .		// references
				' ' . escapeshellarg($sourceDirectory) .	// source directory
				' ' . escapeshellarg($buildPath) .			// build directory
				' 2>&1';									// redirect errors to STDOUT

		$output = array();
		\TYPO3\CMS\Core\Utility\CommandUtility::exec($cmd, $output, $ret);
		$output = implode(LF, $output);
		if ($ret !== 0) {
			throw new \RuntimeException('Cannot build Sphinx project:' . LF . $output, 1366212039);
		}

		$output .= LF;
		$link = $buildPath;
		if (self::$htmlConsole) {
			$properties = \Causal\Sphinx\Utility\Configuration::load($basePath . $conf);
			if ($properties['master_doc']) {
				$uri = substr($basePath, strlen(PATH_site)) . $buildDirectory . '/html/' . $properties['master_doc'] . '.html';
				$link = '<a href="../' . $uri . '" target="sphinx_preview">' . $buildPath . '</a>';
			}
		}
		$output .= 'Build finished. The HTML pages are in ' . $link . '.';

		return $output;
	}

	/**
	 * Builds a Sphinx project as JSON.
	 *
	 * @param string $basePath
	 * @param string $sourceDirectory
	 * @param string $buildDirectory
	 * @param string $conf
	 * @return string Output of the build process (if succeeded)
	 * @throws \RuntimeException if build process failed
	 */
	public static function buildJson($basePath, $sourceDirectory = '.', $buildDirectory = '_build', $conf = '') {
		$sphinxBuilder = self::getSphinxBuilder();

		if (empty($conf)) {
			$conf = '.' . DIRECTORY_SEPARATOR . 'conf.py';
		}
		$basePath = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		$sourceDirectory = rtrim($sourceDirectory);
		$buildDirectory = rtrim($buildDirectory);

		if (!(is_dir($basePath) && (is_file($conf) || is_file($basePath . $conf)))) {
			throw new \RuntimeException('No Sphinx project found in ' . $basePath . $sourceDirectory . DIRECTORY_SEPARATOR, 1366210585);
		}

		$referencesPath = $buildDirectory . DIRECTORY_SEPARATOR . 'doctrees';
		$buildPath = $buildDirectory . DIRECTORY_SEPARATOR . 'json';
		$cmd = 'cd ' . escapeshellarg($basePath) . ' && ' .
			$sphinxBuilder . ' -b json' .					// output format
			' -c ' . escapeshellarg(substr($conf, 0, -7)) .	// directory with configuration file conf.py
			' -d ' . escapeshellarg($referencesPath) .		// references
			' ' . escapeshellarg($sourceDirectory) .		// source directory
			' ' . escapeshellarg($buildPath) .				// build directory
			' 2>&1';										// redirect errors to STDOUT

		$output = array();
		\TYPO3\CMS\Core\Utility\CommandUtility::exec($cmd, $output, $ret);
		$output = implode(LF, $output);
		if ($ret !== 0) {
			throw new \RuntimeException('Cannot build Sphinx project:' . LF . $output, 1366212039);
		}

		$output .= LF;
		$link = $buildPath;
		if (self::$htmlConsole) {
			$properties = \Causal\Sphinx\Utility\Configuration::load($basePath . $conf);
			if ($properties['master_doc']) {
				$uri = substr($basePath, strlen(PATH_site)) . $buildDirectory . '/json/';
				$link = '<a href="../' . $uri . '" target="sphinx_preview">' . $buildPath . '</a>';
			}
		}
		$output .= 'Build finished; now you can process the JSON files in ' . $link . '.';

		return $output;
	}

	/**
	 * Builds a Sphinx project as LaTeX.
	 *
	 * @param string $basePath
	 * @param string $sourceDirectory
	 * @param string $buildDirectory
	 * @param string $conf
	 * @return string Output of the build process (if succeeded)
	 * @throws \RuntimeException if build process failed
	 */
	public static function buildLatex($basePath, $sourceDirectory = '.', $buildDirectory = '_build', $conf = '') {
		$sphinxBuilder = self::getSphinxBuilder();

		if (empty($conf)) {
			$conf = '.' . DIRECTORY_SEPARATOR . 'conf.py';
		}
		$basePath = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		$sourceDirectory = rtrim($sourceDirectory);
		$buildDirectory = rtrim($buildDirectory);
		$paperSize = 'a4';

		if (!(is_dir($basePath) && (is_file($conf) || is_file($basePath . $conf)))) {
			throw new \RuntimeException('No Sphinx project found in ' . $basePath . $sourceDirectory . DIRECTORY_SEPARATOR, 1366210585);
		}

		$referencesPath = $buildDirectory . DIRECTORY_SEPARATOR . 'doctrees';
		$buildPath = $buildDirectory . DIRECTORY_SEPARATOR . 'latex';
		$cmd = 'cd ' . escapeshellarg($basePath) . ' && ' .
			$sphinxBuilder . ' -b latex' .					// output format
			' -c ' . escapeshellarg(substr($conf, 0, -7)) .	// directory with configuration file conf.py
			' -d ' . escapeshellarg($referencesPath) .		// references
			' -D latex_paper_size=' . $paperSize .			// paper size for LaTeX output
			' ' . escapeshellarg($sourceDirectory) .		// source directory
			' ' . escapeshellarg($buildPath) .				// build directory
			' 2>&1';										// redirect errors to STDOUT

		$output = array();
		\TYPO3\CMS\Core\Utility\CommandUtility::exec($cmd, $output, $ret);
		$output = implode(LF, $output);
		if ($ret !== 0) {
			throw new \RuntimeException('Cannot build Sphinx project:' . LF . $output, 1366212039);
		}

		$output .= LF;
		$link = $buildPath;
		if (self::$htmlConsole) {
			$properties = \Causal\Sphinx\Utility\Configuration::load($basePath . $conf);
			if ($properties['master_doc']) {
				$uri = substr($basePath, strlen(PATH_site)) . $buildDirectory . '/latex/';
				$link = '<a href="../' . $uri . '" target="sphinx_preview">' . $buildPath . '</a>';
			}
		}
		$output .= 'Build finished; the LaTeX files are in ' . $link . '.' . LF;
		$output .= 'Run `make\' in that directory to run these through (pdf)latex.';

		return $output;
	}

	/**
	 * Checks links of a Sphinx project.
	 *
	 * @param string $basePath
	 * @param string $sourceDirectory
	 * @param string $buildDirectory
	 * @param string $conf
	 * @return string Output of the check process (if succeeded)
	 * @throws \RuntimeException if check process failed
	 */
	public static function checkLinks($basePath, $sourceDirectory = '.', $buildDirectory = '_build', $conf = '') {
		$sphinxBuilder = self::getSphinxBuilder();

		if (empty($conf)) {
			$conf = '.' . DIRECTORY_SEPARATOR . 'conf.py';
		}
		$basePath = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		$sourceDirectory = rtrim($sourceDirectory);
		$buildDirectory = rtrim($buildDirectory);

		if (!(is_dir($basePath) && (is_file($conf) || is_file($basePath . $conf)))) {
			throw new \RuntimeException('No Sphinx project found in ' . $basePath . $sourceDirectory . DIRECTORY_SEPARATOR, 1366210585);
		}

		$referencesPath = $buildDirectory . DIRECTORY_SEPARATOR . 'doctrees';
		$buildPath = $buildDirectory . DIRECTORY_SEPARATOR . 'linkcheck';
		$cmd = 'cd ' . escapeshellarg($basePath) . ' && ' .
			$sphinxBuilder . ' -b linkcheck' .				// output format
			' -c ' . escapeshellarg(substr($conf, 0, -7)) .	// directory with configuration file conf.py
			' -d ' . escapeshellarg($referencesPath) .		// references
			' ' . escapeshellarg($sourceDirectory) .		// source directory
			' ' . escapeshellarg($buildPath) .				// build directory
			' 2>&1';										// redirect errors to STDOUT

		$output = array();
		\TYPO3\CMS\Core\Utility\CommandUtility::exec($cmd, $output, $ret);
		$output = implode(LF, $output);
		if ($ret !== 0) {
			throw new \RuntimeException('Cannot build Sphinx project:' . LF . $output, 1366212039);
		}

		$output .= LF;
		$link = $buildPath . '/output.txt';
		if (self::$htmlConsole) {
			$properties = \Causal\Sphinx\Utility\Configuration::load($basePath . $conf);
			if ($properties['master_doc']) {
				$uri = substr($basePath, strlen(PATH_site)) . $buildDirectory . '/linkcheck/output.txt';
				$link = '<a href="../' . $uri . '" target="sphinx_preview">' . $buildPath . '/output.txt</a>';
			}
		}
		$output .= 'Link check complete; look for any errors in the above output ';
		$output .= 'or in ' . $link . '.';

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

		$pythonPath = $sphinxPath . 'lib' . DIRECTORY_SEPARATOR . 'python';
		$cmd = 'export PYTHONPATH=' . escapeshellarg($pythonPath) . ' && ' . $sphinxBuilder;
		return $cmd;
	}

}

?>