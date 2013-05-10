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
 */
class ext_update extends \TYPO3\CMS\Backend\Module\BaseScriptClass {

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

		$errors = \Causal\Sphinx\Utility\Setup::createLibraryDirectories();
		if (count($errors) > 0) {
			foreach ($errors as $error) {
				$out[] = $this->formatError($error);
			}
			return implode(LF, $out);
		}

		// Fetch the list of official versions of Sphinx
		$availableVersions = \Causal\Sphinx\Utility\Setup::getSphinxAvailableVersions();
		// Load the list of locally available versions of Sphinx
		$localVersions = \Causal\Sphinx\Utility\Setup::getSphinxLocalVersions();

		// Handle form operation, if needed
		$operation = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('operation');
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
						case \TYPO3\CMS\Core\Utility\GeneralUtility::isFirstPartOfStr($message, '[INFO] '):
							$out[] = $this->formatInformation(substr($message, 7));
							break;
						case \TYPO3\CMS\Core\Utility\GeneralUtility::isFirstPartOfStr($message, '[WARNING] '):
							$out[] = $this->formatWarning(substr($message, 10));
							break;
						case \TYPO3\CMS\Core\Utility\GeneralUtility::isFirstPartOfStr($message, '[ERROR] '):
							$out[] = $this->formatError(substr($message, 8));
							break;
						default:
							$out[] = $message;
							break;
					}
				}

				$logFilename = PATH_site . 'typo3temp/tx_sphinx-' . $action . '-' . date('YmdHis') . '.log';
				\Causal\Sphinx\Utility\Setup::dumpLog($logFilename);

				$out[] = '<p><a href="../' . substr($logFilename, strlen(PATH_site)) . '" target="_blank">Click here</a> to show the complete log.</p>';

				// Reload the list of locally available versions of Sphinx
				$localVersions = \Causal\Sphinx\Utility\Setup::getSphinxLocalVersions();
			}
		}

		$out[] = '<form action="' . \TYPO3\CMS\Core\Utility\GeneralUtility::linkThisScript() . '" method="post">';
		$out[] = '<p>Following versions of Sphinx may be installed locally:</p>';

		$out[] = '<table class="t3-table">';
		$out[] = '<tr class="t3-row-header">';
		$out[] = '<td colspan="2">&nbsp;</td>';
		$out[] = '<td>1-click Process</td>';
		$out[] = '<td>Manual Process</td>';
		$out[] = '</tr>';

		$i = 0;
		foreach ($availableVersions as $version) {
			$isInstalled = \TYPO3\CMS\Core\Utility\GeneralUtility::inArray($localVersions, $version['name']);
			$hasSources = \Causal\Sphinx\Utility\Setup::hasSphinxSources($version['name']);

			$out[] = '<tr class="' . (++$i % 2 == 0 ? 't3-row-even' : 't3-row-odd') . '" style="padding:5px">';
			$out[] = '<td>' . ($isInstalled ? \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('status-status-checked') : '') . '</td>';
			$out[] = '<td>';
			$out[] = 'Sphinx ' . htmlspecialchars($version['name']);
			$out[] = '</td>';

			$out[] = '<td><button name="operation" value="IMPORT-' . htmlspecialchars($version['name']) . '"' . ($isInstalled ? ' disabled="disabled"' : '') . '>import</button></td>';
			$out[] = '<td>';
			$out[] = '<button name="operation" value="DOWNLOAD-' . htmlspecialchars($version['name']) . '"' . ($hasSources ? ' disabled="disabled"' : '') . '>download</button>';
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
		$version = $data['name'];
		$url = 'https://bitbucket.org' . $data['url'];

		if (!\Causal\Sphinx\Utility\Setup::hasSphinxSources($version)) {
			$success &= \Causal\Sphinx\Utility\Setup::downloadSphinxSources($version, $url, $output);
		}
		if (!\Causal\Sphinx\Utility\Setup::hasRestTools()) {
			$success &= \Causal\Sphinx\Utility\Setup::downloadRestTools($output);
		}
		if (!\Causal\Sphinx\Utility\Setup::hasPyYaml()) {
			$success &= \Causal\Sphinx\Utility\Setup::downloadPyYaml($output);
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

		if (\Causal\Sphinx\Utility\Setup::hasSphinxSources($version)) {
			$success = \Causal\Sphinx\Utility\Setup::buildSphinx($version, $output);
			if ($success) {
				if (\Causal\Sphinx\Utility\Setup::hasRestTools()) {
					$success &= \Causal\Sphinx\Utility\Setup::buildRestTools($version, $output);
				}
				if (\Causal\Sphinx\Utility\Setup::hasPyYaml()) {
					$success &= \Causal\Sphinx\Utility\Setup::buildPyYaml($version, $output);
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
		\Causal\Sphinx\Utility\Setup::removeSphinx($version, $output);
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
	 * Creates a warning message for backend output.
	 *
	 * @param string $message
	 * @return string
	 */
	protected function formatWarning($message) {
		$output = '<div style="border: solid 2px black;	background-color: yellow; padding: 10px; font-weight: bold; margin: 10px 0px 10px 0px;">';
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