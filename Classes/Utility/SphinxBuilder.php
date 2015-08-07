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
use TYPO3\CMS\Core\Utility\PathUtility;

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
	static protected $extKey = 'sphinx';

	/** @var boolean */
	static public $htmlConsole = TRUE;

	/**
	 * Returns TRUE if Sphinx is ready.
	 *
	 * @return boolean
	 */
	static public function isReady() {
		try {
			static::getSphinxBuilder();
		} catch (\RuntimeException $e) {
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * Returns TRUE if the version of Sphinx used for building documentation is system.
	 *
	 * @return boolean TRUE if selected version of Sphinx is system, otherwise FALSE
	 */
	static public function isSystemVersion() {
		$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][static::$extKey]);
		return $configuration['version'] === 'SYSTEM';
	}

	/**
	 * Returns TRUE if sphinx-build should auto-recompile a project with faulty extension
	 * (after deactivating it of course).
	 *
	 * @return boolean
	 */
	static protected function autoRecompileWithFaultyExtension() {
		$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][static::$extKey]);
		return $configuration['auto_continue'] !== '0';
	}

	/**
	 * Returns the number of processes to be used for parallel build.
	 *
	 * Using 4 processes is sufficiently conservative to be fine even on
	 * older computers while modern computers have enough core to handle it.
	 * A higher value, with according CPU, is only marginally better.
	 *
	 * @return int
	 */
	static protected function getNumberOfProcesses() {
		$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][static::$extKey]);
		$processes = isset($configuration['processes']) ? (int)$configuration['processes'] : 4;
		return max(1, $processes);
	}

	/**
	 * Returns the version of Sphinx used for building documentation.
	 *
	 * @return string The version of sphinx
	 */
	static public function getSphinxVersion() {
		$version = NULL;
		if (static::isSystemVersion()) {
			$sphinxBuilder = escapeshellarg(\TYPO3\CMS\Core\Utility\CommandUtility::getCommand('sphinx-build'));
			if ($sphinxBuilder) {
				$output = array();
				\TYPO3\CMS\Core\Utility\CommandUtility::exec($sphinxBuilder . ' --version 2>&1', $output);
				$versionLine = $output[0];
				$versionParts = explode(' ', $versionLine);
				$version = end($versionParts);
			}
		} else {
			$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][static::$extKey]);
			$version = $configuration['version'];
		}
		return $version;
	}

	/**
	 * Builds a Sphinx project as HTML.
	 *
	 * @param string $basePath Absolute path to the root directory of the Sphinx project
	 * @param string $sourceDirectory Relative path to the source directory
	 * @param string $buildDirectory Relative path to the build directory
	 * @param string $conf Relative path to the configuration file conf.py
	 * @param string $language Optional language code, see list on http://sphinx-doc.org/latest/config.html#intl-options
	 * @param array $tags Optional tags for sphinx-build (to be used with "only" blocks)
	 * @return string Output of the build process (if succeeded)
	 * @throws \RuntimeException if build process failed
	 */
	static public function buildHtml($basePath, $sourceDirectory = '.', $buildDirectory = '_build', $conf = '', $language = '', array $tags = array()) {
		$sphinxBuilder = static::getSphinxBuilder();

		if (empty($conf)) {
			$conf = './conf.py';
		}
		$basePath = rtrim($basePath, '/') . '/';
		$sourceDirectory = rtrim($sourceDirectory, '/');
		$buildDirectory = rtrim($buildDirectory, '/');

		// Compatibility with Windows platform
		$conf = str_replace('/', DIRECTORY_SEPARATOR, $conf);
		$basePath = str_replace('/', DIRECTORY_SEPARATOR, $basePath);
		$sourceDirectory = str_replace('/', DIRECTORY_SEPARATOR, $sourceDirectory);
		$buildDirectory = str_replace('/', DIRECTORY_SEPARATOR, $buildDirectory);

		if (!(is_dir($basePath) && (is_file($conf) || is_file($basePath . $conf)))) {
			throw new \RuntimeException('No Sphinx project found in ' . $basePath . $sourceDirectory . DIRECTORY_SEPARATOR, 1366210585);
		}

		$referencesPath = $buildDirectory . DIRECTORY_SEPARATOR . 'doctrees';
		$buildPath = $buildDirectory . DIRECTORY_SEPARATOR . 'html';
		$cmd = 'cd ' . escapeshellarg($basePath) . ' && ' .
			$sphinxBuilder . ' -b html' .								// output format
			(count($tags) > 0 ? ' -t ' . implode(' -t ', $tags) : '') .	// define tags
			' -c ' . static::safeEscapeshellarg(substr($conf, 0, -7)) .	// directory with configuration file conf.py
			' -d ' . static::safeEscapeshellarg($referencesPath) .		// references
			' -a -E' .													// always read all files (force compilation)
			(is_writable($basePath) ? ' -w warnings.txt' : '') .		// store warnings and errors to disk
			(!empty($language) ? ' ' . static::getLanguageOption($language) : '') .
			' ' . static::safeEscapeshellarg($sourceDirectory) .		// source directory
			' ' . static::safeEscapeshellarg($buildPath) .				// build directory
			' 2>&1';													// redirect errors to STDOUT

		$output = array();
		static::safeExec($cmd, $output, $ret);
		$output = implode(LF, $output);
		if (static::$htmlConsole) {
			$output = static::colorize($output);
		}
		if ($ret !== 0) {
			if (static::autoRecompileWithFaultyExtension() && static::isTemporaryPath($basePath)) {
				if (preg_match('/Could not import extension ([^ ]+) /', $output, $matches)) {
					if (static::deactivateExtension($basePath . $conf, $matches[1])) {
						$tags[] = 'missing_' . str_replace('.', '_', $matches[1]);
						return static::buildHtml(
							$basePath,
							$sourceDirectory,
							$buildDirectory,
							$conf,
							$language,
							$tags
						);
					}
				}
			}

			throw new \RuntimeException('Cannot build Sphinx project:' . LF . $cmd . LF . $output, 1366212039);
		}

		$output .= LF;
		$link = $buildPath;
		if (static::$htmlConsole) {
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
	 * @param string $basePath Absolute path to the root directory of the Sphinx project
	 * @param string $sourceDirectory Relative path to the source directory
	 * @param string $buildDirectory Relative path to the build directory
	 * @param string $conf Relative path to the configuration file conf.py
	 * @param string $language Optional language code, see list on http://sphinx-doc.org/latest/config.html#intl-options
	 * @param array $tags Optional tags for sphinx-build (to be used with "only" blocks)
	 * @return string Output of the build process (if succeeded)
	 * @throws \RuntimeException if build process failed
	 */
	static public function buildJson($basePath, $sourceDirectory = '.', $buildDirectory = '_build', $conf = '', $language = '', array $tags = array()) {
		$sphinxBuilder = static::getSphinxBuilder();

		if (empty($conf)) {
			$conf = './conf.py';
		}
		$basePath = rtrim($basePath, '/') . '/';
		$sourceDirectory = rtrim($sourceDirectory, '/');
		$buildDirectory = rtrim($buildDirectory, '/');

		// Compatibility with Windows platform
		$conf = str_replace('/', DIRECTORY_SEPARATOR, $conf);
		$basePath = str_replace('/', DIRECTORY_SEPARATOR, $basePath);
		$sourceDirectory = str_replace('/', DIRECTORY_SEPARATOR, $sourceDirectory);
		$buildDirectory = str_replace('/', DIRECTORY_SEPARATOR, $buildDirectory);

		if (!(is_dir($basePath) && (is_file($conf) || is_file($basePath . $conf)))) {
			throw new \RuntimeException('No Sphinx project found in ' . $basePath . $sourceDirectory . DIRECTORY_SEPARATOR, 1366210585);
		}

		$referencesPath = $buildDirectory . DIRECTORY_SEPARATOR . 'doctrees';
		$buildPath = $buildDirectory . DIRECTORY_SEPARATOR . 'json';
		$cmd = 'cd ' . escapeshellarg($basePath) . ' && ' .
			$sphinxBuilder . ' -b json' .								// output format
			(count($tags) > 0 ? ' -t ' . implode(' -t ', $tags) : '') .	// define tags
			' -c ' . static::safeEscapeshellarg(substr($conf, 0, -7)) .	// directory with configuration file conf.py
			' -d ' . static::safeEscapeshellarg($referencesPath) .		// references
			' -a -E' .													// always read all files (force compilation)
			(is_writable($basePath) ? ' -w warnings.txt' : '') .		// store warnings and errors to disk
			(!empty($language) ? ' ' . static::getLanguageOption($language) : '') .
			' ' . static::safeEscapeshellarg($sourceDirectory) .		// source directory
			' ' . static::safeEscapeshellarg($buildPath) .				// build directory
			' 2>&1';													// redirect errors to STDOUT

		$output = array();
		static::safeExec($cmd, $output, $ret);
		$output = implode(LF, $output);
		if (static::$htmlConsole) {
			$output = static::colorize($output);
		}
		if ($ret !== 0) {
			if (static::autoRecompileWithFaultyExtension() && static::isTemporaryPath($basePath)) {
				if (preg_match('/Could not import extension ([^ ]+) /', $output, $matches)) {
					if (static::deactivateExtension($basePath . $conf, $matches[1])) {
						$tags[] = 'missing_' . str_replace('.', '_', $matches[1]);
						return static::buildJson(
							$basePath,
							$sourceDirectory,
							$buildDirectory,
							$conf,
							$language,
							$tags
						);
					}
				}
			}

			throw new \RuntimeException('Cannot build Sphinx project:' . LF . $cmd . LF . $output, 1366212039);
		}

		$output .= LF;
		$link = $buildPath;
		if (static::$htmlConsole) {
			$uri = substr($basePath, strlen(PATH_site)) . $buildDirectory . '/json/';
			$link = '<a href="../' . $uri . '" target="sphinx_preview">' . $buildPath . '</a>';
		}
		$output .= 'Build finished; now you can process the JSON files in ' . $link . '.';

		return $output;
	}

	/**
	 * Builds a Sphinx project as LaTeX.
	 *
	 * @param string $basePath Absolute path to the root directory of the Sphinx project
	 * @param string $sourceDirectory Relative path to the source directory
	 * @param string $buildDirectory Relative path to the build directory
	 * @param string $conf Relative path to the configuration file conf.py
	 * @param string $language Optional language code, see list on http://sphinx-doc.org/latest/config.html#intl-options
	 * @param array $tags Optional tags for sphinx-build (to be used with "only" blocks)
	 * @return string Output of the build process (if succeeded)
	 * @throws \RuntimeException if build process failed
	 */
	static public function buildLatex($basePath, $sourceDirectory = '.', $buildDirectory = '_build', $conf = '', $language = '', array $tags = array()) {
		$sphinxBuilder = static::getSphinxBuilder();

		if (empty($conf)) {
			$conf = './conf.py';
		}
		$basePath = rtrim($basePath, '/') . '/';
		$sourceDirectory = rtrim($sourceDirectory, '/');
		$buildDirectory = rtrim($buildDirectory, '/');
		$paperSize = 'a4';
		$sphinxSourcesPath = GeneralUtility::getFileAbsFileName('uploads/tx_sphinx/');
		$templatePath = $sphinxSourcesPath . 'RestTools/LaTeX/';
		$templateFiles = array(
			'typo3.sty',
			'typo3_logo_color.png',
		);

		// Compatibility with Windows platform
		$conf = str_replace('/', DIRECTORY_SEPARATOR, $conf);
		$basePath = str_replace('/', DIRECTORY_SEPARATOR, $basePath);
		$sourceDirectory = str_replace('/', DIRECTORY_SEPARATOR, $sourceDirectory);
		$buildDirectory = str_replace('/', DIRECTORY_SEPARATOR, $buildDirectory);
		$templatePath = str_replace('/', DIRECTORY_SEPARATOR, $templatePath);

		if (!(is_dir($basePath) && (is_file($conf) || is_file($basePath . $conf)))) {
			throw new \RuntimeException('No Sphinx project found in ' . $basePath . $sourceDirectory . DIRECTORY_SEPARATOR, 1366210585);
		}

		$referencesPath = $buildDirectory . DIRECTORY_SEPARATOR . 'doctrees';
		$buildPath = $buildDirectory . DIRECTORY_SEPARATOR . 'latex';
		$cmd = 'cd ' . escapeshellarg($basePath) . ' && ' .
			$sphinxBuilder . ' -b latex' .								// output format
			(count($tags) > 0 ? ' -t ' . implode(' -t ', $tags) : '') .	// define tags
			' -c ' . static::safeEscapeshellarg(substr($conf, 0, -7)) .	// directory with configuration file conf.py
			' -d ' . static::safeEscapeshellarg($referencesPath) .		// references
			' -a -E' .													// always read all files (force compilation)
			(is_writable($basePath) ? ' -w warnings.txt' : '') .		// store warnings and errors to disk
			(!empty($language) ? ' ' . static::getLanguageOption($language) : '') .
			' -D latex_paper_size=' . $paperSize .						// paper size for LaTeX output
			' ' . static::safeEscapeshellarg($sourceDirectory) .		// source directory
			' ' . static::safeEscapeshellarg($buildPath) .				// build directory
			' 2>&1';													// redirect errors to STDOUT

		$output = array();
		static::safeExec($cmd, $output, $ret);
		$output = implode(LF, $output);
		if (static::$htmlConsole) {
			$output = static::colorize($output);
		}
		foreach ($templateFiles as $templateFile) {
			copy($templatePath . $templateFile, $basePath . $buildPath . DIRECTORY_SEPARATOR . $templateFile);
		}
		if ($ret !== 0) {
			if (static::autoRecompileWithFaultyExtension() && static::isTemporaryPath($basePath)) {
				if (preg_match('/Could not import extension ([^ ]+) /', $output, $matches)) {
					if (static::deactivateExtension($basePath . $conf, $matches[1])) {
						$tags[] = 'missing_' . str_replace('.', '_', $matches[1]);
						return static::buildLatex(
							$basePath,
							$sourceDirectory,
							$buildDirectory,
							$conf,
							$language,
							$tags
						);
					}
				}
			}

			throw new \RuntimeException('Cannot build Sphinx project:' . LF . $cmd . LF . $output, 1366212039);
		}

		$texFileNames = glob($basePath . $buildPath . DIRECTORY_SEPARATOR . '*.tex');
		if (is_array($texFileNames) && count($texFileNames) > 0) {
			$texFileName = $texFileNames[0];

			/** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
			$objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
			/** @var $signalSlotDispatcher \TYPO3\CMS\Extbase\SignalSlot\Dispatcher */
			$signalSlotDispatcher = $objectManager->get('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');

			$signalSlotDispatcher->dispatch(
				__CLASS__,
				'afterBuildLatex',
				array(
					'texFileName' => $texFileName,
					'basePath' => $basePath,
					'sourceDirectory' => $sourceDirectory,
					'buildDirectory' => $buildDirectory,
					'conf' => $conf,
					'language' => $language,
				)
			);
		}

		$output .= LF;
		$link = $buildPath;
		if (static::$htmlConsole) {
			$uri = substr($basePath, strlen(PATH_site)) . $buildDirectory . '/latex/';
			$link = '<a href="../' . $uri . '" target="sphinx_preview">' . $buildPath . '</a>';
		}
		$output .= 'Build finished; the LaTeX files are in ' . $link . '.' . LF;
		$output .= 'Run `make\' in that directory to run these through (pdf)latex.';

		return $output;
	}

	/**
	 * Builds a Sphinx project as PDF.
	 *
	 * @param string $basePath Absolute path to the root directory of the Sphinx project
	 * @param string $sourceDirectory Relative path to the source directory
	 * @param string $buildDirectory Relative path to the build directory
	 * @param string $conf Relative path to the configuration file conf.py
	 * @param string $language Optional language code, see list on http://sphinx-doc.org/latest/config.html#intl-options
	 * @param array $tags Optional tags for sphinx-build (to be used with "only" blocks)
	 * @return string Output of the build process (if succeeded)
	 * @throws \RuntimeException if build process failed
	 */
	static public function buildPdf($basePath, $sourceDirectory = '.', $buildDirectory = '_build', $conf = '', $language = '', array $tags = array()) {
		$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][static::$extKey]);

		switch ($configuration['pdf_builder']) {
			case 'pdflatex':
				$output = static::buildPdfWithLaTeX($basePath, $sourceDirectory, $buildDirectory, $conf, $language, $tags);
			break;
			case 'rst2pdf':
				$output = static::buildPdfWithRst2Pdf($basePath, $sourceDirectory, $buildDirectory, $conf, $language, $tags);
			break;
			default:
				throw new \RuntimeException('No available PDF builders.', 1378718863);
		}

		return $output;
	}

	/**
	 * Builds a Sphinx project as PDF using pdflatex on a LaTeX project.
	 *
	 * @param string $basePath Absolute path to the root directory of the Sphinx project
	 * @param string $sourceDirectory Relative path to the source directory
	 * @param string $buildDirectory Relative path to the build directory
	 * @param string $conf Relative path to the configuration file conf.py
	 * @param string $language Optional language code, see list on http://sphinx-doc.org/latest/config.html#intl-options
	 * @param array $tags Optional tags for sphinx-build (to be used with "only" blocks)
	 * @return string Output of the build process (if succeeded)
	 * @throws \RuntimeException if build process failed
	 */
	static protected function buildPdfWithLaTeX($basePath, $sourceDirectory = '.', $buildDirectory = '_build', $conf = '', $language = '', array $tags = array()) {
		$make = \TYPO3\CMS\Core\Utility\CommandUtility::getCommand('make');
		$pdflatex = \TYPO3\CMS\Core\Utility\CommandUtility::getCommand('pdflatex');

		if (TYPO3_OS !== 'WIN' && empty($make)) {
			throw new \RuntimeException('Command `make\' was not found.', 1367239044);
		}
		if (empty($pdflatex)) {
			throw new \RuntimeException('Command `pdflatex\' was not found.', 1367239067);
		}

		if (empty($conf)) {
			$conf = './conf.py';
		}
		$basePath = rtrim($basePath, '/') . '/';
		$sourceDirectory = rtrim($sourceDirectory);
		$buildDirectory = rtrim($buildDirectory);

		// Compatibility with Windows platform
		$conf = str_replace('/', DIRECTORY_SEPARATOR, $conf);
		$basePath = str_replace('/', DIRECTORY_SEPARATOR, $basePath);
		$sourceDirectory = str_replace('/', DIRECTORY_SEPARATOR, $sourceDirectory);
		$buildDirectory = str_replace('/', DIRECTORY_SEPARATOR, $buildDirectory);

		if (!(is_dir($basePath) && (is_file($conf) || is_file($basePath . $conf)))) {
			throw new \RuntimeException('No Sphinx project found in ' . $basePath . $sourceDirectory . DIRECTORY_SEPARATOR, 1366210585);
		}

		$outputLaTeX = static::buildLatex($basePath, $sourceDirectory, $buildDirectory, $conf, $language, $tags);

		$buildPath = $buildDirectory . DIRECTORY_SEPARATOR . 'latex';
		if (!empty($make)) {
			$cmd = 'cd ' . escapeshellarg($basePath) . ' && ' .
				MiscUtility::getExportCommand('PATH', '"$PATH' . PATH_SEPARATOR . PathUtility::dirname($pdflatex) . '"') . ' && ' .
				escapeshellarg($make) . ' -C ' . static::safeEscapeshellarg($buildDirectory . '/latex') . ' clean all-pdf' .
				' 2>&1';	// redirect errors to STDOUT
		} else {
			// We are on Windows and "make" is not available,
			// we will thus simulate it
			$latexPath = $basePath . $buildDirectory . DIRECTORY_SEPARATOR . 'latex' . DIRECTORY_SEPARATOR;
			$files = GeneralUtility::getFilesInDir($latexPath, 'tex');
			$mainFile = current($files);
			$basename = substr($mainFile, 0, -4);	// Remove .tex
			$makeindex = \TYPO3\CMS\Core\Utility\CommandUtility::getCommand('makeindex');
			// List of patterns extracted from a generated Makefile
			$cleanCmd = 'del /Q *.dvi *.log *.ind *.aux *.toc *.syn *.idx *.out *.ilg *.pla';

			$cmd = 'cd ' . escapeshellarg($latexPath) . ' && ' .
				MiscUtility::getExportCommand('PATH', '"$PATH' . PATH_SEPARATOR . PathUtility::dirname($pdflatex) . '"') . ' && ' .
				$cleanCmd . ' && ' .
				escapeshellarg($pdflatex) . ' ' . $mainFile . ' && ' .
				escapeshellarg($pdflatex) . ' ' . $mainFile . ' && ' .
				escapeshellarg($pdflatex) . ' ' . $mainFile . ' && ' .
				escapeshellarg($makeindex) . ' -s python.ist ' . $basename . '.idx' . ' && ' .
				escapeshellarg($pdflatex) . ' ' . $mainFile . ' && ' .
				escapeshellarg($pdflatex) . ' ' . $mainFile .
				' 2>&1';	// redirect errors to STDOUT
		}

		$output = array('Running LaTeX files through pdflatex...');
		static::safeExec($cmd, $output, $ret);
		$output = implode(LF, $output);
		if (static::$htmlConsole) {
			$output = static::colorize($output);
		}
		// Prepend previous command output
		$output = $outputLaTeX . LF . $output;
		if ($ret !== 0) {
			throw new \RuntimeException('Cannot build Sphinx project:' . LF . $cmd . LF . $output, 1366212039);
		}

		$output .= LF;
		$link = $buildPath;
		if (static::$htmlConsole) {
			$properties = \Causal\Sphinx\Utility\Configuration::load($basePath . $conf);
			if ($properties['project']) {
				$latexProject = str_replace(' ', '', $properties['project']);
				$uri = substr($basePath, strlen(PATH_site)) . $buildDirectory . '/latex/' . $latexProject . '.pdf';
				$link = '<a href="../' . $uri . '" target="sphinx_preview">' . $buildPath . '</a>';
			}
		}
		$output .= 'pdflatex finished; the PDF file is in ' . $link . '.' . LF;

		return $output;
	}

	/**
	 * Builds a Sphinx project as PDF using rst2pdf.
	 *
	 * @param string $basePath Absolute path to the root directory of the Sphinx project
	 * @param string $sourceDirectory Relative path to the source directory
	 * @param string $buildDirectory Relative path to the build directory
	 * @param string $conf Relative path to the configuration file conf.py
	 * @param string $language Optional language code, see list on http://sphinx-doc.org/latest/config.html#intl-options
	 * @param array $tags Optional tags for sphinx-build (to be used with "only" blocks)
	 * @return string Output of the build process (if succeeded)
	 * @throws \RuntimeException if build process failed
	 */
	static protected function buildPdfWithRst2Pdf($basePath, $sourceDirectory = '.', $buildDirectory = '_build', $conf = '', $language = '', array $tags = array()) {
		$sphinxBuilder = static::getSphinxBuilder();

		if (empty($conf)) {
			$conf = './conf.py';
		}
		$basePath = rtrim($basePath, '/') . '/';
		$sourceDirectory = rtrim($sourceDirectory);
		$buildDirectory = rtrim($buildDirectory);

		// Compatibility with Windows platform
		$conf = str_replace('/', DIRECTORY_SEPARATOR, $conf);
		$basePath = str_replace('/', DIRECTORY_SEPARATOR, $basePath);
		$sourceDirectory = str_replace('/', DIRECTORY_SEPARATOR, $sourceDirectory);
		$buildDirectory = str_replace('/', DIRECTORY_SEPARATOR, $buildDirectory);

		if (!(is_dir($basePath) && (is_file($conf) || is_file($basePath . $conf)))) {
			throw new \RuntimeException('No Sphinx project found in ' . $basePath . $sourceDirectory . DIRECTORY_SEPARATOR, 1372003205);
		}

		$referencesPath = $buildDirectory . DIRECTORY_SEPARATOR . 'doctrees';
		$buildPath = $buildDirectory . DIRECTORY_SEPARATOR . 'pdf';
		$cmd = 'cd ' . escapeshellarg($basePath) . ' && ' .
			$sphinxBuilder . ' -b pdf' .								// output format
			(count($tags) > 0 ? ' -t ' . implode(' -t ', $tags) : '') .	// define tags
			' -c ' . static::safeEscapeshellarg(substr($conf, 0, -7)) .	// directory with configuration file conf.py
			' -d ' . static::safeEscapeshellarg($referencesPath) .		// references
			' -a -E' .													// always read all files (force compilation)
			(is_writable($basePath) ? ' -w warnings.txt' : '') .		// store warnings and errors to disk
			(!empty($language) ? ' ' . static::getLanguageOption($language) : '') .
			' ' . static::safeEscapeshellarg($sourceDirectory) .			// source directory
			' ' . static::safeEscapeshellarg($buildPath) .				// build directory
			' 2>&1';													// redirect errors to STDOUT

		$output = array();
		static::safeExec($cmd, $output, $ret);
		$output = implode(LF, $output);
		if (static::$htmlConsole) {
			$output = static::colorize($output);
		}
		if ($ret !== 0 || preg_match('/\[ERROR\] pdfbuilder\.py/', $output)) {
			if (static::autoRecompileWithFaultyExtension() && static::isTemporaryPath($basePath)) {
				if (preg_match('/Could not import extension ([^ ]+) /', $output, $matches)) {
					if (static::deactivateExtension($basePath . $conf, $matches[1])) {
						$tags[] = 'missing_' . str_replace('.', '_', $matches[1]);
						return static::buildPdfWithRst2Pdf(
							$basePath,
							$sourceDirectory,
							$buildDirectory,
							$conf,
							$language,
							$tags
						);
					}
				}
			}

			throw new \RuntimeException('Cannot build Sphinx project:' . LF . $cmd . LF . $output, 1372003276);
		}

		$output .= LF;
		$link = $buildPath;
		if (static::$htmlConsole) {
			$properties = \Causal\Sphinx\Utility\Configuration::load($basePath . $conf);
			if ($properties['project']) {
				$project = str_replace(' ', '', $properties['project']);
				$uri = substr($basePath, strlen(PATH_site)) . $buildDirectory . '/pdf/' . $project . '.pdf';
				$link = '<a href="../' . $uri . '" target="sphinx_preview">' . $buildPath . '</a>';
			}
		}
		$output .= 'rst2pdf finished; the PDF file is in ' . $link . '.' . LF;

		return $output;
	}

	/**
	 * Checks links of a Sphinx project.
	 *
	 * @param string $basePath Absolute path to the root directory of the Sphinx project
	 * @param string $sourceDirectory Relative path to the source directory
	 * @param string $buildDirectory Relative path to the build directory
	 * @param string $conf Relative path to the configuration file conf.py
	 * @param string $language Optional language code, see list on http://sphinx-doc.org/latest/config.html#intl-options
	 * @return string Output of the check process (if succeeded)
	 * @throws \RuntimeException if check process failed
	 */
	static public function checkLinks($basePath, $sourceDirectory = '.', $buildDirectory = '_build', $conf = '', $language = '') {
		$sphinxBuilder = static::getSphinxBuilder();

		if (empty($conf)) {
			$conf = './conf.py';
		}
		$basePath = rtrim($basePath, '/') . '/';
		$sourceDirectory = rtrim($sourceDirectory);
		$buildDirectory = rtrim($buildDirectory);

		// Compatibility with Windows platform
		$conf = str_replace('/', DIRECTORY_SEPARATOR, $conf);
		$basePath = str_replace('/', DIRECTORY_SEPARATOR, $basePath);
		$sourceDirectory = str_replace('/', DIRECTORY_SEPARATOR, $sourceDirectory);
		$buildDirectory = str_replace('/', DIRECTORY_SEPARATOR, $buildDirectory);

		if (!(is_dir($basePath) && (is_file($conf) || is_file($basePath . $conf)))) {
			throw new \RuntimeException('No Sphinx project found in ' . $basePath . $sourceDirectory . DIRECTORY_SEPARATOR, 1366210585);
		}

		$referencesPath = $buildDirectory . DIRECTORY_SEPARATOR . 'doctrees';
		$buildPath = $buildDirectory . DIRECTORY_SEPARATOR . 'linkcheck';
		$cmd = 'cd ' . escapeshellarg($basePath) . ' && ' .
			$sphinxBuilder . ' -b linkcheck' .							// output format
			' -c ' . static::safeEscapeshellarg(substr($conf, 0, -7)) .	// directory with configuration file conf.py
			' -d ' . static::safeEscapeshellarg($referencesPath) .		// references
			' -a -E' .													// always read all files (force compilation)
			(is_writable($basePath) ? ' -w warnings.txt' : '') .		// store warnings and errors to disk
			(!empty($language) ? ' ' . static::getLanguageOption($language) : '') .
			' ' . static::safeEscapeshellarg($sourceDirectory) .			// source directory
			' ' . static::safeEscapeshellarg($buildPath) .				// build directory
			' 2>&1';													// redirect errors to STDOUT

		$output = array();
		static::safeExec($cmd, $output, $ret);
		$output = implode(LF, $output);
		if (static::$htmlConsole) {
			$output = static::colorize($output);
		}
		if ($ret !== 0) {
			throw new \RuntimeException('Cannot build Sphinx project:' . LF . $cmd . LF . $output, 1366212039);
		}

		$output .= LF;
		$link = $buildPath . '/output.txt';
		if (static::$htmlConsole) {
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
	 * @return string Command(s) to run sphinx-build
	 * @throws \RuntimeException
	 */
	static protected function getSphinxBuilder() {
		$sphinxVersion = static::getSphinxVersion();
		if (static::isSystemVersion()) {
			$sphinxBuilder = \TYPO3\CMS\Core\Utility\CommandUtility::getCommand('sphinx-build');
			while (is_link($sphinxBuilder)) {
				$link = readlink($sphinxBuilder);
				if ($link{0} === '/') {
					// Absolute symbolic link
					$sphinxBuilder = $link;
				} else {
					// Relative symbolic link
					$sphinxBuilder = realpath(PathUtility::dirname($sphinxBuilder) . '/' . $link);
				}
			}
			$sphinxPath = substr($sphinxBuilder, 0, strrpos($sphinxBuilder, '/bin/') + 1);
		} else {
			$sphinxPath = GeneralUtility::getFileAbsFileName(
					'typo3temp/tx_sphinx/sphinx-doc/' . $sphinxVersion . '/'
			);
			$sphinxBuilder = $sphinxPath . 'bin/sphinx-build';

			if (TYPO3_OS === 'WIN') {
				$sphinxBuilder .= '.exe';
			}
		}

		// Compatibility with Windows platform
		$sphinxBuilder = str_replace('/', DIRECTORY_SEPARATOR, $sphinxBuilder);

		if (empty($sphinxVersion)) {
			throw new \RuntimeException('Sphinx is not configured. Please use Extension Manager.', 1366210198);
		} elseif (!is_executable($sphinxBuilder)) {
			throw new \RuntimeException('Sphinx ' . $sphinxVersion . ' (' . $sphinxBuilder . ') cannot be executed.', 1366280021);
		}

		$pythonPath = $sphinxPath . 'lib/python';

		// Compatibility with Windows platform
		$pythonPath = str_replace('/', DIRECTORY_SEPARATOR, $pythonPath);

		$exports = array(
			MiscUtility::getExportCommand('PYTHONPATH', $pythonPath)
		);
		if (static::$htmlConsole) {
			$exports[] = MiscUtility::getExportCommand('COLORTERM', '1');
		}
		$cmd = implode(' && ', $exports) . ' && ' . escapeshellarg($sphinxBuilder);
		if (version_compare($sphinxVersion, '1.2', '>=')) {
			// Speed up the rendering by using "-j" flag:
			// https://bitbucket.org/birkenfeld/sphinx/commits/a00063ecdb9649b7bab1f084be55c4511d1bbdce
			$processes = static::getNumberOfProcesses();
			if ($processes > 1) {
				$cmd .= ' -j' . $processes;
			}
		}
		return $cmd;
	}

	/**
	 * Colorizes a shell output using HTML markers.
	 *
	 * @param string $output Shell output
	 * @return string Colorized shell output
	 */
	static protected function colorize($output) {
		// Shell colors
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
		$output = preg_replace($ESC_SEQ . '01m/', '<span>', $output);
		foreach ($mapping as $pattern => $html) {
			$output = preg_replace($pattern, $html, $output);
		}

		return $output;
	}

	/**
	 * Returns the list of supported locales for Sphinx.
	 *
	 * @return array Array of locale names, indexed by language code/locale
	 * @see http://sphinx-doc.org/latest/config.html#intl-options
	 */
	static public function getSupportedLocales() {
		return array(
			'bn'    => 'Bengali',
			'ca'    => 'Catalan',
			'cs'    => 'Czech',
			'da'    => 'Danish',
			'de'    => 'German',
			//'en'  => 'English',
			'es'    => 'Spanish',
			'et'    => 'Estonian',
			'eu'    => 'Basque',
			'fa'    => 'Iranian',
			'fi'    => 'Finnish',
			'fr'    => 'French',
			'hr'    => 'Croatian',
			'hu'    => 'Hungarian',
			'id'    => 'Indonesian',
			'it'    => 'Italian',
			'ja'    => 'Japanese',
			'ko'    => 'Korean',
			'lt'    => 'Lithuanian',
			'lv'    => 'Latvian',
			'mk'    => 'Macedonian',
			'nb_NO' => 'Norwegian Bokmal',
			'ne'    => 'Nepali',
			'nl'    => 'Dutch',
			'pl'    => 'Polish',
			'pt_BR' => 'Brazilian Portuguese',
			'ru'    => 'Russian',
			'si'    => 'Sinhala',
			'sk'    => 'Slovak',
			'sl'    => 'Slovenian',
			'sv'    => 'Swedish',
			'tr'    => 'Turkish',
			'uk_UA' => 'Ukrainian',
			'zh_CN' => 'Simplified Chinese',
			'zh_TW' => 'Traditional Chinese',
		);
	}

	/**
	 * Returns a language compilation command option for Sphinx.
	 *
	 * @param string $languageCode Language code or locale supported by Sphinx
	 * @return string Command option to use the language code with sphinx-build
	 * @see \Causal\Sphinx\Utility\SphinxBuilder::getSupportedLocales()
	 */
	static protected function getLanguageOption($languageCode) {
		$locale = '';
		$supportedLocales = static::getSupportedLocales();

		if (isset($supportedLocales[$languageCode])) {
			$locale = $languageCode;
		} elseif (($pos = strpos($languageCode, '_')) !== FALSE) {
			$languageCode = substr($languageCode, 0, $pos);
			if (isset($supportedLocales[$languageCode])) {
				$locale = $languageCode;
			}
		}

		return !empty($locale) ? '-D language=' . $locale : '';
	}

	/**
	 * Deactivates an extension both in conf.py and Settings.yml.
	 *
	 * @param string $filename Absolute filename to conf.py
	 * @param string $extension Extension to deactivate
	 * @return boolean TRUE if deactivation succeeded, otherwise FALSE
	 */
	static protected function deactivateExtension($filename, $extension) {
		$extensionIsDeactivated = FALSE;
		$contents = file_get_contents($filename);

		$newContents = preg_replace(
			'/(extensions = \[.*?)\'' . preg_quote($extension) . '\'(,\s*)?(.*?\])/',
			'\1\3',
			$contents
		);

		if ($contents !== $newContents) {
			$extensionIsDeactivated = GeneralUtility::writeFile($filename, $newContents);
		}

		$settingsYmlFilename = PathUtility::dirname($filename) . '/../Settings.yml';
		if (is_file($settingsYmlFilename)) {
			$contents = file_get_contents($settingsYmlFilename);
			$newContents = preg_replace(
				'/\s+- ' . preg_quote($extension) . '/',
				'',
				$contents
			);

			if ($contents !== $newContents) {
				$extensionIsDeactivated = GeneralUtility::writeFile($settingsYmlFilename, $newContents);
			}
		}

		return $extensionIsDeactivated;
	}

	/**
	 * Returns TRUE if $path is within the TYPO3 temporary path.
	 *
	 * @param string $path
	 * @return boolean
	 */
	static protected function isTemporaryPath($path) {
		$temporaryPath = GeneralUtility::getFileAbsFileName('typo3temp/');
		// Compatibility with Windows platform
		$temporaryPath = str_replace('/', DIRECTORY_SEPARATOR, $temporaryPath);

		return GeneralUtility::isFirstPartOfStr($path, $temporaryPath);
	}

	/**
	 * Escape a string to be used as a shell argument
	 *
	 * @param string $arg String to be escaped
	 * @return string Escaped string
	 */
	static protected function safeEscapeshellarg($arg) {
		if (!(TYPO3_OS === 'WIN' && strpos($arg, ' ') === FALSE)) {
			$arg = escapeshellarg($arg);
		}
		return $arg;
	}

	/**
	 * Wrapper function for TYPO3 exec function.
	 *
	 * @param string $command Command to execute
	 * @param NULL|array $output Shell output
	 * @param integer $returnValue Return code of the command
	 * @return void
	 */
	static protected function safeExec($command, &$output = NULL, &$returnValue = 0) {
		if (TYPO3_OS === 'WIN' && strpos($command, ' && ') !== FALSE) {
			// Multiple commands are not supported on Windows
			// We use an intermediate batch file instead
			$relativeBatchFilename = 'typo3temp/tx_' . static::$extKey . '/build-' . $GLOBALS['EXEC_TIME'] . '.bat';
			$absoluteBatchFilename = GeneralUtility::getFileAbsFileName($relativeBatchFilename);
			$batchScript = '@ECHO OFF' . CR . LF . str_replace(' && ', CR . LF, $command);

			GeneralUtility::writeFile($absoluteBatchFilename, $batchScript);
			\TYPO3\CMS\Core\Utility\CommandUtility::exec($absoluteBatchFilename, $output, $returnValue);

			@unlink($absoluteBatchFilename);
		} else {
			\TYPO3\CMS\Core\Utility\CommandUtility::exec($command, $output, $returnValue);
		}
	}

}
