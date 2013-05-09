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

		$availableVersions = \Causal\Sphinx\Utility\Setup::getSphinxAvailableVersions();
		$localVersions = \Causal\Sphinx\Utility\Setup::getSphinxLocalVersions();
		$importVersion = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('sphinx_version');
		if ($importVersion && isset($availableVersions[$importVersion]) && !\TYPO3\CMS\Core\Utility\GeneralUtility::inArray($localVersions, $importVersion)) {
			$messages = array();
			$this->importSphinx($availableVersions[$importVersion], $messages);
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
			$localVersions = \Causal\Sphinx\Utility\Setup::getSphinxLocalVersions();
		}

		$out[] = '<form action="' . \TYPO3\CMS\Core\Utility\GeneralUtility::linkThisScript() . '" method="post">';
		$out[] = '<p>Following versions of Sphinx may be installed locally:</p>';
		$out[] = '<div style="-moz-column-count:3;-webkit-column-count:3;column-count:3;margin-top:1ex;">';

		$i = 0;
		foreach ($availableVersions as $version) {
			$out[] = '<div style="margin-bottom:1ex">';
			$disabled = \TYPO3\CMS\Core\Utility\GeneralUtility::inArray($localVersions, $version['name']) ? ' disabled="disabled"' : '';
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

		$out[] = '</div>';
		$out[] = '<button type="submit" style="margin-top:1ex">Import selected version of Sphinx</button>';
		$out[] = '</form>';

		return implode(LF, $out);
	}

	/**
	 * Imports a given version from Sphinx.
	 *
	 * @param array $data
	 * @param array &$output
	 * @return void
	 */
	protected function importSphinx(array $data, array &$output) {
		$version = $data['name'];
		$url = 'https://bitbucket.org' . $data['url'];

		// STEP 1: Download Sphinx archive as zip
		$success = \Causal\Sphinx\Utility\Setup::downloadSphinxSources($version, $url, $output);

		if ($success) {
			// STEP 2: Build Sphinx
			$success = \Causal\Sphinx\Utility\Setup::buildSphinx($version, $output);

			if ($success) {
				// STEP 3: Download TYPO3 ReST Tools
				if (\Causal\Sphinx\Utility\Setup::downloadRestTools($output)) {
					// STEP 4: Build TYPO3 ReST Tools
					\Causal\Sphinx\Utility\Setup::buildRestTools($version, $output);
				}

				// STEP 5: Download PyYAML
				if (\Causal\Sphinx\Utility\Setup::downloadPyYaml($output)) {
					// STEP 6: Build PyYAML
					\Causal\Sphinx\Utility\Setup::buildPyYaml($version, $output);
				}
			}
		}

		$logFilename = PATH_site . 'typo3temp/sphinx-import.' . date('YmdHis') . '.log';
		\Causal\Sphinx\Utility\Setup::dumpLog($logFilename);
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