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
 * SphinxBuilder Wrapper.
 *
 * @category    Utility
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class SphinxBuilder {

	/** @var string */
	protected static $extKey = 'sphinx';

	/** @var boolean */
	public static $htmlConsole = TRUE;

	/**
	 * Returns TRUE if the version of Sphinx used for building documentation is system.
	 *
	 * @return boolean
	 */
	public static function isSystemVersion() {
		$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][self::$extKey]);
		return $configuration['version'] === 'SYSTEM';
	}

	/**
	 * Returns the version of Sphinx used for building documentation.
	 *
	 * @return string
	 */
	public static function getSphinxVersion() {
		$version = NULL;
		if (self::isSystemVersion()) {
			$sphinxBuilder = \TYPO3\CMS\Core\Utility\CommandUtility::getCommand('sphinx-build');
			if ($sphinxBuilder) {
				$output = array();
				\TYPO3\CMS\Core\Utility\CommandUtility::exec($sphinxBuilder . ' --version 2>&1', $output);
				$versionLine = $output[0];
				$versionParts = explode(' ', $versionLine);
				$version = end($versionParts);
			}
		} else {
			$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][self::$extKey]);
			$version = $configuration['version'];
		}
		return $version;
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
		if (self::$htmlConsole) {
			$output = self::colorize($output);
		}
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
		if (self::$htmlConsole) {
			$output = self::colorize($output);
		}
		if ($ret !== 0) {
			throw new \RuntimeException('Cannot build Sphinx project:' . LF . $output, 1366212039);
		}

		$output .= LF;
		$link = $buildPath;
		if (self::$htmlConsole) {
			$uri = substr($basePath, strlen(PATH_site)) . $buildDirectory . '/json/';
			$link = '<a href="../' . $uri . '" target="sphinx_preview">' . $buildPath . '</a>';
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
		if (self::$htmlConsole) {
			$output = self::colorize($output);
		}
		if ($ret !== 0) {
			throw new \RuntimeException('Cannot build Sphinx project:' . LF . $output, 1366212039);
		}

		$output .= LF;
		$link = $buildPath;
		if (self::$htmlConsole) {
			$uri = substr($basePath, strlen(PATH_site)) . $buildDirectory . '/latex/';
			$link = '<a href="../' . $uri . '" target="sphinx_preview">' . $buildPath . '</a>';
		}
		$output .= 'Build finished; the LaTeX files are in ' . $link . '.' . LF;
		$output .= 'Run `make\' in that directory to run these through (pdf)latex.';

		return $output;
	}

	/**
	 * Builds a Sphinx project as PDF using pdflatex on a LaTeX project.
	 *
	 * @param string $basePath
	 * @param string $sourceDirectory
	 * @param string $buildDirectory
	 * @param string $conf
	 * @return string Output of the build process (if succeeded)
	 * @throws \RuntimeException if build process failed
	 */
	public static function buildPdf($basePath, $sourceDirectory = '.', $buildDirectory = '_build', $conf = '') {
		$make = \TYPO3\CMS\Core\Utility\CommandUtility::getCommand('make');
		$pdflatex = \TYPO3\CMS\Core\Utility\CommandUtility::getCommand('pdflatex');

		if (empty($make)) {
			throw new \RuntimeException('Command `make\' was not found.', 1367239044);
		}
		if (empty($pdflatex)) {
			throw new \RuntimeException('Command `pdflatex\' was not found.', 1367239067);
		}

		if (empty($conf)) {
			$conf = '.' . DIRECTORY_SEPARATOR . 'conf.py';
		}
		$basePath = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		$sourceDirectory = rtrim($sourceDirectory);
		$buildDirectory = rtrim($buildDirectory);

		if (!(is_dir($basePath) && (is_file($conf) || is_file($basePath . $conf)))) {
			throw new \RuntimeException('No Sphinx project found in ' . $basePath . $sourceDirectory . DIRECTORY_SEPARATOR, 1366210585);
		}

		$outputLaTeX = self::buildLatex($basePath, $sourceDirectory, $buildDirectory, $conf);

		$buildPath = $buildDirectory . DIRECTORY_SEPARATOR . 'latex';
		$cmd = 'cd ' . escapeshellarg($basePath) . ' && ' .
			'export PATH="$PATH' . PATH_SEPARATOR . dirname($pdflatex) . '" && ' .
			$make . ' -C ' . escapeshellarg($buildDirectory . '/latex') . ' all-pdf' .
			' 2>&1';	// redirect errors to STDOUT

		$output = array('Running LaTeX files through pdflatex...');
		\TYPO3\CMS\Core\Utility\CommandUtility::exec($cmd, $output, $ret);
		$output = implode(LF, $output);
		if (self::$htmlConsole) {
			$output = self::colorize($output);
		}
		// Prepend previous command output
		$output = $outputLaTeX . $output;
		if ($ret !== 0) {
			throw new \RuntimeException('Cannot build Sphinx project:' . LF . $output, 1366212039);
		}

		$output .= LF;
		$link = $buildPath;
		if (self::$htmlConsole) {
			$properties = \Causal\Sphinx\Utility\Configuration::load($basePath . $conf);
			if ($properties['project']) {
				$latexProject = str_replace(' ', '', $properties['project']);
				$uri = substr($basePath, strlen(PATH_site)) . $buildDirectory . '/latex/' . $latexProject . '.pdf';
				$link = '<a href="../' . $uri . '" target="sphinx_preview">' . $buildPath . '</a>';
			}
		}
		$output .= 'pdflatex finished; the PDF files are in ' . $link . '.' . LF;

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
		if (self::$htmlConsole) {
			$output = self::colorize($output);
		}
		if ($ret !== 0) {
			throw new \RuntimeException('Cannot build Sphinx project:' . LF . $output, 1366212039);
		}

		$output .= LF;
		$link = $buildPath . '/output.txt';
		if (self::$htmlConsole) {
			$uri = substr($basePath, strlen(PATH_site)) . $buildDirectory . '/linkcheck/output.txt';
			$link = '<a href="../' . $uri . '" target="sphinx_preview">' . $buildPath . '/output.txt</a>';
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
		if (self::isSystemVersion()) {
			$sphinxBuilder = \TYPO3\CMS\Core\Utility\CommandUtility::getCommand('sphinx-build');
			while (is_link($sphinxBuilder)) {
				$sphinxBuilder = readlink($sphinxBuilder);
			}
			$sphinxPath = substr($sphinxBuilder, 0, strrpos($sphinxBuilder, '/bin/') + 1);
		} else {
			$sphinxPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath(self::$extKey) . 'Resources/Private/sphinx/' . $sphinxVersion . '/';
			$sphinxBuilder = $sphinxPath . 'bin/sphinx-build';
		}

		if (empty($sphinxVersion)) {
			throw new \RuntimeException('Sphinx is not configured. Please use Extension Manager.', 1366210198);
		} elseif (!is_executable($sphinxBuilder)) {
			throw new \RuntimeException('Sphinx ' . $sphinxVersion . ' cannot be executed.', 1366280021);
		}

		$pythonPath = $sphinxPath . 'lib' . DIRECTORY_SEPARATOR . 'python';
		$exports = array(
			'export PYTHONPATH=' . escapeshellarg($pythonPath)
		);
		if (self::$htmlConsole) {
			$exports[] = 'export COLORTERM=1';
		}
		$cmd = implode(' && ', $exports) . ' && ' . $sphinxBuilder;
		return $cmd;
	}

	/**
	 * Colorizes a shell output using HTML markers.
	 *
	 * @param string $output
	 * @return string
	 */
	protected function colorize($output) {
		# Shell colors
		$ESC_SEQ     = '/[\x00-\x1F\x7F]\[';
		$COL_BLACK   = $ESC_SEQ . '30(;01)?m/';
		$COL_RED     = $ESC_SEQ . '31(;01)?m/';
		$COL_GREEN   = $ESC_SEQ . '32(;01)?m/';
		$COL_YELLOW  = $ESC_SEQ . '33(;01)?m/';
		$COL_BLUE    = $ESC_SEQ . '34(;01)?m/';
		$COL_MAGENTA = $ESC_SEQ . '35(;01)?m/';
		$COL_CYAN    = $ESC_SEQ . '36(;01)?m/';
		$COL_GRAY    = $ESC_SEQ . '37(;01)?m/';
		$COL_RESET   = $ESC_SEQ . '39;49;00m/';

		$mapping = array(
			$COL_BLACK   => '<span style="color:#000000">',
			$COL_RED     => '<span style="color:#dc143c">',
			$COL_GREEN   => '<span style="color:#228B22">',
			$COL_YELLOW  => '<span style="color:#ffd700">',
			$COL_BLUE    => '<span style="color:#6495ed">',
			$COL_MAGENTA => '<span style="color:#ba55d3">',
			$COL_CYAN    => '<span style="color:#00ffff">',
			$COL_GRAY    => '<span style="color:#a9a9a9">',
			$COL_RESET   => '</span>',
		);
		$output = preg_replace($ESC_SEQ . '01m/', '', $output);
		foreach ($mapping as $pattern => $html) {
			$output = preg_replace($pattern, $html, $output);
		}

		return $output;
	}

}

?>