<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013-2014 Xavier Perseguers <xavier@causal.ch>
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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Causal\Sphinx\Utility\Setup;

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
 */
class ext_update extends \TYPO3\CMS\Backend\Module\BaseScriptClass {

	/** @var string */
	protected $extKey = 'sphinx';

	/** @var array */
	protected $configuration;

	/**
	 * Default constructor.
	 */
	public function __construct() {
		$this->configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
	}

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

		$errors = Setup::createLibraryDirectories();
		if (count($errors) > 0) {
			foreach ($errors as $error) {
				$out[] = $this->formatError($error);
			}
			return implode(LF, $out);
		}

		// Sphinx actually relies by default on Jinja2 as templating engine and Jinja2 requires Python 2.6
		// We don't check this as a hard requirement but will issue a warning
		$pythonVersion = Setup::getPythonVersion();
		if (version_compare($pythonVersion, '2.6', '<')) {
			$out[] = $this->formatWarning('The default templating language in Sphinx is Jinja. Jinja requires at least ' .
				'Python 2.6 but you are using ' . $pythonVersion . '. As such it is very likely that you will not be ' .
				'able to actually render Sphinx projects.');
		}

		// Fetch the list of official versions of Sphinx
		$report = array();
		$availableVersions = Setup::getSphinxAvailableVersions();
		// Load the list of locally available versions of Sphinx
		$localVersions = Setup::getSphinxLocalVersions();

		if (count($availableVersions) == 0) {
			$message = <<<HTML
Could not find any version of Sphinx:<br /><br />
<ul>
	<li>Are you currently offline?</li>
	<li>Does PHP have proper OpenSSL support?</li>
HTML;

			if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['curlUse'] == '1') {
				$message .= '<li>You have $GLOBALS[\'TYPO3_CONF_VARS\'][\'SYS\'][\'curlUse\'] == \'1\', ' .
					'your OpenSSL configuration might be broken</li>';
			}

			$message .= '</ul>';

			$out[] = $this->formatWarning($message, FALSE);
		}

		// Handle form operation, if needed
		$operation = GeneralUtility::_POST('operation');
		if ($operation) {
			list($action, $version) = explode('-', $operation, 2);

			if (isset($availableVersions[$version])) {
				$messages = array();

				switch ($action) {
					case 'DOWNLOAD':
						$this->downloadSphinx($availableVersions[$version], $messages);
					break;
					case 'BUILD':
						$this->buildSphinx($availableVersions[$version], $messages);
					break;
					case 'IMPORT':
						if ($this->downloadSphinx($availableVersions[$version], $messages)) {
							$this->buildSphinx($availableVersions[$version], $messages);
						}
					break;
					case 'REMOVE':
						$this->removeSphinx($availableVersions[$version], $messages);
					break;
				}

				foreach ($messages as $message) {
					switch (TRUE) {
						case GeneralUtility::isFirstPartOfStr($message, '[OK] '):
							$out[] = $this->formatOk(substr($message, 5));
						break;
						case GeneralUtility::isFirstPartOfStr($message, '[INFO] '):
							$out[] = $this->formatInformation(substr($message, 7));
						break;
						case GeneralUtility::isFirstPartOfStr($message, '[WARNING] '):
							$out[] = $this->formatWarning(substr($message, 10));
						break;
						case GeneralUtility::isFirstPartOfStr($message, '[ERROR] '):
							$out[] = $this->formatError(substr($message, 8));
						break;
						default:
							$out[] = $message;
						break;
					}
				}

				$relativeLogFilename = 'typo3temp/tx_sphinx/' . $action . '-' . date('YmdHis') . '.log';
				$absoluteLogFilename = GeneralUtility::getFileAbsFileName($relativeLogFilename);
				Setup::dumpLog($absoluteLogFilename);

				$out[] = '<p><a href="../' . $relativeLogFilename . '" target="_blank">Click here</a> to show the complete log.</p>';

				// Reload the list of locally available versions of Sphinx
				$localVersions = Setup::getSphinxLocalVersions();
			}
		}

		$out[] = '<form action="' . GeneralUtility::linkThisScript() . '" method="post">';
		$out[] = '<p>Following versions of Sphinx may be installed locally:</p>';

		$out[] = '<table class="typo3-dblist" style="width:auto">';
		$out[] = '<tr class="t3-row-header">';
		$out[] = '<td colspan="2">&nbsp;</td>';
		$out[] = '<td>1-click Process</td>';
		$out[] = '<td>Manual Process</td>';
		$out[] = '</tr>';

		$installRst2Pdf = TYPO3_OS !== 'WIN' && $this->configuration['install_rst2pdf'] === '1';

		$i = 0;
		foreach ($availableVersions as $version) {
			$isInstalled = GeneralUtility::inArray($localVersions, $version['name']);
			$hasSources = Setup::hasSphinxSources($version['name']);
			$hasLibraries = Setup::hasPyYaml()
				&& Setup::hasPygments()
				&& Setup::hasRestTools()
				&& Setup::hasThirdPartyLibraries();
			if ($installRst2Pdf) {
				$hasLibraries &= Setup::hasPIL();
				$hasLibraries &= Setup::hasRst2Pdf();
			}

			$out[] = '<tr class="' . (++$i % 2 == 0 ? 't3-row-even' : 't3-row-odd') . '" style="padding:5px">';
			$out[] = '<td>' . ($isInstalled ? \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('status-status-checked') : '') . '</td>';
			$out[] = '<td>';
			$out[] = 'Sphinx ' . htmlspecialchars($version['name']);
			$out[] = '</td>';

			$out[] = '<td><button name="operation" value="IMPORT-' . htmlspecialchars($version['name']) . '"' . ($isInstalled ? ' disabled="disabled"' : '') . '>import</button></td>';
			$out[] = '<td>';
			$out[] = '<button name="operation" value="DOWNLOAD-' . htmlspecialchars($version['name']) . '"' . ($hasSources && $hasLibraries ? ' disabled="disabled"' : '') . '>download</button>';
			$out[] = '<button name="operation" value="BUILD-' . htmlspecialchars($version['name']) . '"' . (!$hasSources ? ' disabled="disabled"' : '') . '>build</button>';
			$out[] = '<button name="operation" value="REMOVE-' . htmlspecialchars($version['name']) . '"' . (!($hasSources || $isInstalled) ? ' disabled="disabled"' : '') . '>remove</button>';
			$out[] = '</td>';
			$out[] = '</tr>';
		}
		$out[] = '</table>';
		$out[] = '</form>';

		return implode(LF, $out);
	}

	/**
	 * Downloads Sphinx and associated libraries.
	 *
	 * @param array $data
	 * @param array &$output
	 * @return boolean TRUE if operation succeeded, otherwise FALSE
	 */
	protected function downloadSphinx(array $data, array &$output) {
		$success = TRUE;
		$installRst2Pdf = TYPO3_OS !== 'WIN' && $this->configuration['install_rst2pdf'] === '1';
		$version = $data['name'];
		$url = 'https://bitbucket.org' . $data['url'];

		if (!Setup::hasSphinxSources($version)) {
			$success &= Setup::downloadSphinxSources($version, $url, $output);
		}
		if ($installRst2Pdf && !Setup::hasPIL()) {
			$success &= Setup::downloadPIL($output);
		}
		if ($installRst2Pdf && !Setup::hasRst2Pdf()) {
			$success &= Setup::downloadRst2Pdf($output);
		}
		if (!Setup::hasPyYaml()) {
			$success &= Setup::downloadPyYaml($output);
		}
		if (!Setup::hasRestTools()) {
			$success &= Setup::downloadRestTools($output);
		}
		if (!Setup::hasThirdPartyLibraries()) {
			$success &= Setup::downloadThirdPartyLibraries($output);
		}
		if (!Setup::hasPygments()) {
			$success &= Setup::downloadPygments($output);
		}

		return $success;
	}

	/**
	 * Builds Sphinx associated libraries.
	 *
	 * @param array $data
	 * @param array &$output
	 * @return boolean TRUE if operation succeeded, otherwise FALSE
	 */
	protected function buildSphinx(array $data, array &$output) {
		$success = FALSE;
		$version = $data['name'];
		$installRst2Pdf = TYPO3_OS !== 'WIN' && $this->configuration['install_rst2pdf'] === '1';

		if (Setup::hasSphinxSources($version)) {
			$success = Setup::buildSphinx($version, $output);
			if ($success) {
				if ($installRst2Pdf && Setup::hasPIL()) {
					$success &= Setup::buildPIL($version, $output);
				}
				if ($installRst2Pdf && Setup::hasRst2Pdf()) {
					$success &= Setup::buildRst2Pdf($version, $output);
				}
				if (Setup::hasThirdPartyLibraries()) {
					$selectedPlugins = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->configuration['plugins'], TRUE);
					foreach ($selectedPlugins as $selectedPlugin) {
						$success &= Setup::buildThirdPartyLibraries($selectedPlugin, $version, $output);
					}
				}
				if (Setup::hasPyYaml()) {
					$success &= Setup::buildPyYaml($version, $output);
				}
				if (Setup::hasPygments()) {
					$success &= Setup::buildPygments($version, $output);
				}
				if (Setup::hasRestTools()) {
					$success &= Setup::buildRestTools($version, $output);
				}
			}
		}

		return $success;
	}

	/**
	 * Removes Sphinx (but not associated libraries).
	 *
	 * @param array $data
	 * @param array &$output
	 * @return void
	 */
	protected function removeSphinx(array $data, array &$output) {
		$version = $data['name'];
		Setup::removeSphinx($version, $output);
	}

	/**
	 * Creates an error message for backend output.
	 *
	 * @param string $message
	 * @return string
	 */
	protected function formatError($message) {
		$output = '<div class="typo3-message message-error">';
		//$output .= '<div class="message-header">Message head</div>';
		$output .= '<div class="message-body">' . nl2br(htmlspecialchars($message)) . '</div>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Creates a warning message for backend output.
	 *
	 * @param string $message
	 * @param boolean $hsc
	 * @return string
	 */
	protected function formatWarning($message, $hsc = TRUE) {
		$output = '<div class="typo3-message message-warning">';
		//$output .= '<div class="message-header">Message head</div>';
		if ($hsc) {
			$message = nl2br(htmlspecialchars($message));
		}
		$output .= '<div class="message-body">' . $message . '</div>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Creates an information message for backend output.
	 *
	 * @param string $message
	 * @param boolean $hsc
	 * @return string
	 */
	protected function formatInformation($message, $hsc = TRUE) {
		$output = '<div class="typo3-message message-information">';
		//$output .= '<div class="message-header">Message head</div>';
		if ($hsc) {
			$message = nl2br(htmlspecialchars($message));
		}
		$output .= '<div class="message-body">' . $message . '</div>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Creates an OK message for backend output.
	 *
	 * @param string $message
	 * @param boolean $hsc
	 * @return string
	 */
	protected function formatOk($message, $hsc = TRUE) {
		$output = '<div class="typo3-message message-ok">';
		//$output .= '<div class="message-header">Message head</div>';
		if ($hsc) {
			$message = nl2br(htmlspecialchars($message));
		}
		$output .= '<div class="message-body">' . $message . '</div>';
		$output .= '</div>';

		return $output;
	}

}
