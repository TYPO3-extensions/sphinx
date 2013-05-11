<?php
namespace Causal\Sphinx\EM;

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
 * Configuration class for the TYPO3 Extension Manager.
 *
 * @category    Extension Manager
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Configuration {

	/** @var string */
	protected $extKey = 'sphinx';

	/**
	 * Returns an Extension Manager field for selecting the Sphinx version to use.
	 *
	 * @param array $params
	 * @param \TYPO3\CMS\Extensionmanager\ViewHelpers\Form\TypoScriptConstantsViewHelper $pObj
	 * @return string
	 */
	public function getVersions(array $params, \TYPO3\CMS\Extensionmanager\ViewHelpers\Form\TypoScriptConstantsViewHelper $pObj) {
		$out = array();

		$sphinxPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($this->extKey) . 'Resources/Private/sphinx';
		$versions = array();
		if (is_dir($sphinxPath)) {
			$versions = \TYPO3\CMS\Core\Utility\GeneralUtility::get_dirs($sphinxPath);
		}
		$versions = array_diff($versions, array('bin'));

		if (!$versions) {
			$out[] = 'No versions of Sphinx available. Please run Update script first.';
		}

		$selectedVersion = $params['fieldValue'];

		if ($selectedVersion) {
			// Recreate the shortcut links to selected version
			// /path/to/sphinx/sphinx-build -> /path/to/sphinx/sphinx-build-1.2b1
			$scripts = array(
				'sphinx-build',
				'sphinx-quickstart',
			);
			chdir($sphinxPath . '/bin');
			foreach ($scripts as $script) {
				@unlink($sphinxPath . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . $script);
				symlink($script . '-' . $selectedVersion, $script);
			}
		}

		$i = 0;
		foreach ($versions as $version) {
			$out[] = '<div style="margin-top:1ex">';
			$checked = $version === $selectedVersion ? ' checked="checked"' : '';
			$out[] = '<input type="radio" id="sphinx_version_' . $i . '" name="sphinx_version" value="' . $version . '"' . $checked . ' onclick="toggleSphinxVersion();" />';
			$out[] = '<label for="sphinx_version_' . $i . '" style="display:inline">' . $version . '</label>';
			$out[] = '</div>';
			$i++;
		}

		$fieldId = str_replace(array('[', ']'), '_', $params['fieldName']);
		$out[] = '<script type="text/javascript">';
		$out[] = <<<JS

function toggleSphinxVersion() {
	var versions = document.getElementsByName('sphinx_version');
	for (var i = 0; i < versions.length; i++) {
		if (versions[i].checked) {
			document.getElementById("{$fieldId}").value = versions[i].value;
		}
	}
}

JS;
		$out[] = '</script>';
		$out[] = '<input type="hidden" id="' . $fieldId . '" name="' . $params['fieldName'] .  '" value="' . $params['fieldValue'] . '" />';

		return implode(LF, $out);
	}

}

?>