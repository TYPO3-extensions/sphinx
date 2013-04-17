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


$BACK_PATH = $GLOBALS['BACK_PATH'] . TYPO3_mainDir;

/**
 * Class to be used to initialize the Sphinx Python Documentation Generator locally.
 *
 * @category    Initialization Modules
 * @package     tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   2013 Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class ext_update extends t3lib_SCbase {

	/** @var string */
	protected $extKey = 'sphinx';

	/**
	 * Checks whether the "UPDATE!" menu item should be
	 * shown.
	 *
	 * @return boolean
	 */
	public function access() {
		return TRUE;
	}

	/**
	 * Main method that is called whenever UPDATE! menu
	 * was clicked.
	 *
	 * @return string HTML to display
	 */
	public function main() {
		$content = '';

		$python = $this->getPython();
		if (!$python['cmd']) {
			$content .= $this->formatError('Python interpreter was not found.');
			return $content;
		}

		$content .= $this->formatInformation(
			sprintf('%s found as %s.', $python['version'], $python['cmd'])
		);

		$privateDirectory = t3lib_extMgm::extPath($this->extKey) . 'Resources/Private/';

		if (!file_exists($privateDirectory)) {
			t3lib_div::mkdir_deep($privateDirectory);
			t3lib_div::mkdir_deep($privateDirectory . 'tmp');
			t3lib_div::mkdir_deep($privateDirectory . 'sphinx');
			t3lib_div::mkdir_deep($privateDirectory . 'sphinx-sources');
		}

		if (!is_writable($privateDirectory . 'tmp/')) {
			$content .= $this->formatError('Directory Resources/Private/tmp/ is not writable');
			return $content;
		}
		if (!is_writable($privateDirectory . 'sphinx-sources/')) {
			$content .= $this->formatError('Directory Resources/Private/sphinx-sources/ is not writable');
			return $content;
		}
		if (!is_writable($privateDirectory . 'sphinx/')) {
			$content .= $this->formatError('Directory Resources/Private/sphinx/ is not writable');
			return $content;
		}

		// TEST (begin)
		/*
		t3lib_div::rmdir($privateDirectory . 'tmp/test', TRUE);
		t3lib_div::mkdir($privateDirectory . 'tmp/test');
		$cmd = 'cd ' . escapeshellarg($privateDirectory . 'tmp/test/') . ' && ' .
			'export PYTHONPATH=' . escapeshellarg($privateDirectory. 'sphinx/1.1.3/lib/python') . ' && ' .
			$privateDirectory . 'sphinx/1.1.3/bin/sphinx-quickstart . 2>&1 &';
		*/
		/*
		$cmd = 'cd ' . escapeshellarg($privateDirectory . 'tmp/test/') . ' && ' .
			'export PYTHONPATH=' . escapeshellarg($privateDirectory. 'sphinx/1.1.3/lib/python') . ' && ' .
			$privateDirectory . 'sphinx/1.1.3/bin/sphinx-build -b html -d _build/doctrees . _build/html 2>&1';
		t3lib_utility_Debug::debug($cmd, 'cmd');
		$output = array();
		t3lib_utility_Command::exec($cmd, $output);
		return $this->formatInformation(implode(LF, $output));
		*/
		// TEST (end)

		$zipFile = $privateDirectory . 'tmp/1.1.3.zip';
		if (!file_exists($zipFile)) {
			$sphinxSources = 'https://bitbucket.org/birkenfeld/sphinx/get/1.1.3.zip';
			$zip = t3lib_div::getUrl($sphinxSources);
			if ($zip && t3lib_div::writeFile($zipFile, $zip)) {
				$content .= $this->formatInformation('Sphinx 1.1.3 has been downloaded.');

				$target = $privateDirectory . 'sphinx-sources/1.1.3';
				t3lib_div::rmdir($target, TRUE);
				t3lib_div::mkdir($target);

				$unzipPath = trim($GLOBALS['TYPO3_CONF_VARS']['BE']['unzip_path']);
				if (substr($unzipPath, -1) !== '/' && is_dir($unzipPath)) {
					// Make sure the path ends with a slash
					$unzipPath .= '/';
				}
				if (is_dir($unzipPath)) {
					$unzipPath .= 'unzip';
				}
				$cmd = $unzipPath . ' -qq ' . escapeshellarg($zipFile) . ' -d ' . escapeshellarg($target);
				t3lib_utility_Command::exec($cmd, $_, $ret);
				if ($ret === 0) {
					$content .= $this->formatInformation('Sphinx 1.1.3 has been unpacked to Resources/Private/sphinx-sources/1.1.3.');
					// When unzipping the sources, content is located under a directory "birkenfeld-sphinx-<hash>"
					$directories = t3lib_div::get_dirs($target);
					if (t3lib_div::isFirstPartOfStr($directories[0], 'birkenfeld-sphinx-')) {
						$fromDirectory = escapeshellarg($target . '/' . $directories[0]);
						$cmd = 'mv ' . $fromDirectory . '/* ' . escapeshellarg($target . '/');
						t3lib_utility_Command::exec($cmd);
						t3lib_div::rmdir($target . '/' . $directories[0], TRUE);
					} else {
						$content .= $this->formatError('Could not locate folder starting with birkenfeld-sphinx- in Resources/Private/sphinx-sources/1.1.3/.');
					}
				} else {
					$content .= $this->formatError('Could not extract Sphinx 1.1.3 to Resources/Private/sphinx-sources/1.1.3:' . LF . $cmd);
				}
			}
		}
		$setupFile = $privateDirectory . 'sphinx-sources/1.1.3/setup.py';
		if (is_file($setupFile)) {
			$cmd = 'cd ' . escapeshellarg(dirname($setupFile)) . ' && ' .
				$python['cmd'] . ' setup.py build';
			$output = array();
			t3lib_utility_Command::exec($cmd, $output, $ret);
			if ($ret === 0) {
				$target = $privateDirectory . 'sphinx/1.1.3';
				t3lib_div::rmdir($target, TRUE);
				t3lib_div::mkdir_deep($target . '/lib/python');

				$cmd = 'cd ' . escapeshellarg(dirname($setupFile)) . ' && ' .
					'export PYTHONPATH=' . escapeshellarg($target. '/lib/python') . ' && ' .
					$python['cmd'] . ' setup.py install --home=' . escapeshellarg($target) . ' 2>&1';
				$output = array();
				t3lib_utility_Command::exec($cmd, $output, $ret);
				if ($ret !== 0) {
					$content .= $this->formatError('Could not install Sphinx 1.1.3:' . LF . LF . implode($output, LF));
				}
			} else {
				$content .= $this->formatError('Could not build Sphinx 1.1.3:' . LF . LF . implode($output, LF));
			}
		}

		return $content;
	}

	/**
	 * Retrieves python executable information.
	 *
	 * @return array
	 */
	protected function getPython() {
		// TODO: use configuration's binPath?
		$paths = array(
			'/usr/bin/',
			'/usr/local/bin/',
		);
		$result = array(
			'cmd'     => '',
			'version' => '',
		);

		// Try default command first
		$cmd = t3lib_utility_Command::exec('which dpython', $_, $ret);
		if ($ret === 0) {
			$result['cmd'] = $cmd;
		} else {
			foreach ($paths as $path) {
				$files = t3lib_div::getFilesInDir($path);
				$files = array_filter(
					$files,
					function($e) use ($path) {
						return preg_match('/^python(-?[0-9.-]+)?$/', $e) && is_executable($path . $e);
					}
				);
				if (count($files) > 0) {
					// Take the highest version available
					rsort($files);
					$result['cmd'] = $path . $files[0];
					break;
				}
			}
		}

		if ($result['cmd']) {
			// Retrieve the version of python
			$result['version'] = t3lib_utility_Command::exec($result['cmd'] . ' --version 2>&1');
		}

		return $result;
	}

	/**
	 * Creates an error message for backend output.
	 *
	 * @param string $message
	 * @return string
	 */
	protected function formatError($message) {
		$output = '<div style="border: solid 2px black;	background-color: #f00; color: #fff; padding: 10px; font-weight: bold; margin: 10px 0px 10px 0px;">';
		$output .= nl2br(htmlspecialchars($message));
		$output .= '</div>';

		return $output;
	}

	/**
	 * Creates an information message for backend output.
	 *
	 * @param string $message
	 * @return string
	 */
	protected function formatInformation($message) {
		$output = '<div style="border: solid 2px black;	background-color: lightblue; padding: 10px; font-weight: bold; margin: 10px 0px 10px 0px;">';
		$output .= nl2br(htmlspecialchars($message));
		$output .= '</div>';

		return $output;
	}
}

?>