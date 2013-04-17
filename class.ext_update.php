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
 * @category    Extension Manager
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   2013 Causal Sàrl
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @version     SVN: $Id$
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
		$out = array();

		$errors = $this->initializeEnvironment();
		if (count($errors) > 0) {
			foreach ($errors as $error) {
				$out[] = $this->formatError($error);
			}
			return implode(LF, $out);
		}

		$availableVersions = $this->getSphinxAvailableVersions();
		$localVersions = $this->getLocalVersions();
		$importVersion = t3lib_div::_POST('sphinx_version');
		if ($importVersion && isset($availableVersions[$importVersion]) && !t3lib_div::inArray($localVersions, $importVersion)) {
			$this->importSphinx($availableVersions[$importVersion], $out);
			$localVersions = $this->getLocalVersions();
		}

		$out[] = '<form action="' . t3lib_div::linkThisScript() . '" method="post">';
		$out[] = '<p>Following versions of Sphinx may be installed locally:</p>';

		$i = 0;
		foreach ($availableVersions as $version) {
			$out[] = '<div style="margin-top:1ex">';
			$disabled = t3lib_div::inArray($localVersions, $version['name']) ? ' disabled="disabled"' : '';
			$out[] = '<input type="radio" id="sphinx_version_' . $i . '" name="sphinx_version" value="' . htmlspecialchars($version['name']) . '"' . $disabled . ' />';
			$label = '<label for="sphinx_version_' . $i . '">';
			if ($disabled) {
				$label .= '<strong>' . htmlspecialchars($version['name']) . '</strong> (available locally)';
			} else {
				$label .= htmlspecialchars($version['name']);
			}
			$label .= '</label>';
			$out[] = $label;
			$out[] = '</div>';
			$i++;
		}

		$out[] = '<button type="submit">Import</button>';
		$out[] = '</form>';

		return implode(LF, $out);
	}

	/**
	 * Initializes the environment and returns error messages, if any.
	 *
	 * @return array
	 */
	protected function initializeEnvironment() {
		$errors = array();

		if (!t3lib_exec::checkCommand('python')) {
			$errors[] = 'Python interpreter was not found.';
		}
		if (!t3lib_exec::checkCommand('unzip')) {
			$errors[] = 'Unzip cannot be executed.';
		}

		$directories = array(
			'Resources/Private/sphinx',
			'Resources/Private/sphinx-sources',
			'Resources/Private/tmp',
		);
		foreach ($directories as $directory) {
			$path = t3lib_extMgm::extPath($this->extKey) . $directory;
			if (!is_dir($path)) {
				t3lib_div::mkdir_deep($path);
			}
			if (is_dir($path)) {
				if (!is_writable($path)) {
					$errors[] = 'Directory ' . $directory . ' is read-only.';
				}
			} else {
				$errors[] = 'Cannot create directory ' . $directory . '.';
			}
		}

		return $errors;
	}

	/**
	 * Returns a list of online available versions of Sphinx.
	 *
	 * @return array
	 */
	protected function getSphinxAvailableVersions() {
		$sphinxUrl = 'https://bitbucket.org/birkenfeld/sphinx/downloads';

		$cacheFilename = PATH_site . 'typo3temp/' . $this->extKey . '.' . md5($sphinxUrl) . '.html';
		if (!file_exists($cacheFilename) || filemtime($cacheFilename) < (time() - 86400)) {
			$html = t3lib_div::getURL($sphinxUrl);
			t3lib_div::writeFile($cacheFilename, $html);
		} else {
			$html = file_get_contents($cacheFilename);
		}

		$tagsHtml = substr($html, strpos($html, '<section class="tabs-pane" id="tag-downloads">'));
		$tagsHtml = substr($tagsHtml, 0, strpos($tagsHtml, '</section>'));

		$versions = array();
		preg_replace_callback(
			'#<tr class="iterable-item">.*?<td class="name"><a href="[^>]+>([^<]*)</a></td>.*?<a href="([^"]+)">zip</a>#s',
			function($matches) use (&$versions) {
				if ($matches[1] !== 'tip') {
					$versions[$matches[1]] = array(
						'name' => $matches[1],
						'url'  => $matches[2],
					);
				}
			},
			$tagsHtml
		);

		krsort($versions);
		return $versions;
	}

	/**
	 * Returns a list of locally available versions of Sphinx.
	 *
	 * @return array
	 */
	protected function getLocalVersions() {
		$sphinxPath = t3lib_extMgm::extPath($this->extKey) . 'Resources/Private/sphinx';
		$versions = array();
		if (is_dir($sphinxPath)) {
			$versions = t3lib_div::get_dirs($sphinxPath);
		}
		return $versions;
	}

	/**
	 * Imports a given version from Sphinx.
	 *
	 * @param array $data
	 * @param array &$out
	 * @return void
	 */
	protected function importSphinx(array $data, array &$out) {
		$version = $data['name'];
		$url = 'https://bitbucket.org' . $data['url'];

		$tempPath = t3lib_extMgm::extPath($this->extKey) . 'Resources/Private/tmp/';
		$sphinxSourcesPath = t3lib_extMgm::extPath($this->extKey) . 'Resources/Private/sphinx-sources/';
		$sphinxPath = t3lib_extMgm::extPath($this->extKey) . 'Resources/Private/sphinx/';

		$zipFilename = $tempPath . $version . 'zip';
		$zipContent = t3lib_div::getUrl($url);
		if ($zipContent && t3lib_div::writeFile($zipFilename, $zipContent)) {
			$out[] = $this->formatInformation('Sphinx ' . $version . ' has been downloaded.');
			$targetPath = $sphinxSourcesPath . $version;
			t3lib_div::rmdir($targetPath, TRUE);
			t3lib_div::mkdir($targetPath);

			$unzip = t3lib_exec::getCommand('unzip');
			$cmd = $unzip . ' -qq ' . escapeshellarg($zipFilename) . ' -d ' . escapeshellarg($targetPath);
			t3lib_utility_Command::exec($cmd, $_, $ret);
			if ($ret === 0) {
				$out[] = $this->formatInformation('Sphinx ' . $version . ' has been unpacked.');
				// When unzipping the sources, content is located under a directory "birkenfeld-sphinx-<hash>"
				$directories = t3lib_div::get_dirs($targetPath);
				if (t3lib_div::isFirstPartOfStr($directories[0], 'birkenfeld-sphinx-')) {
					$fromDirectory = escapeshellarg($targetPath . '/' . $directories[0]);
					$cmd = 'mv ' . $fromDirectory . '/* ' . escapeshellarg($targetPath . '/');
					t3lib_utility_Command::exec($cmd);
					t3lib_div::rmdir($targetPath . '/' . $directories[0], TRUE);
				}
			} else {
				$out[] = $this->formatError('Could not extract Sphinx ' . $version . ':' . LF . $cmd);
			}
		} else {
			$out[] = $this->formatError('Cannot fetch file ' . $url . '.');
		}

		$setupFile = $sphinxSourcesPath . $version . '/setup.py';
		if (is_file($setupFile)) {
			$python = t3lib_exec::getCommand('python');
			$cmd = 'cd ' . escapeshellarg(dirname($setupFile)) . ' && ' .
				$python . ' setup.py build';
			$output = array();
			t3lib_utility_Command::exec($cmd, $output, $ret);
			if ($ret === 0) {
				$targetPath = $sphinxPath . $version;
				t3lib_div::rmdir($targetPath, TRUE);
				t3lib_div::mkdir_deep($targetPath . '/lib/python');

				$cmd = 'cd ' . escapeshellarg(dirname($setupFile)) . ' && ' .
					'export PYTHONPATH=' . escapeshellarg($targetPath . '/lib/python') . ' && ' .
					$python . ' setup.py install --home=' . escapeshellarg($targetPath) . ' 2>&1';
				$output = array();
				t3lib_utility_Command::exec($cmd, $output, $ret);
				if ($ret === 0) {
					$out[] = $this->formatInformation('Sphinx ' . $version . ' has successfully been installed.');
				} else {
					$out[] = $this->formatError('Could not install Sphinx ' . $version . ':' . LF . LF . implode($output, LF));
				}
			} else {
				$out[] = $this->formatError('Could not build Sphinx ' . $version . ':' . LF . LF . implode($output, LF));
			}
		}
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