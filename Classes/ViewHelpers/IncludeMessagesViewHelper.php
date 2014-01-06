<?php
namespace Causal\Sphinx\ViewHelpers;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Xavier Perseguers <xavier@causal.ch>
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
 * Includes localized messages.
 *
 * @category    ViewHelpers
 * @package     tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class IncludeMessagesViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Renders the JS snippet.
	 *
	 * @param string $keyPrefix
	 * @param string $jsDictionnary
	 * @return string
	 */
	public function render($keyPrefix, $jsDictionnary) {
		$llFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('sphinx') . 'Resources/Private/Language/locallang.xlf';
		$labels = $GLOBALS['LANG']->includeLLFile($llFile, FALSE);
		$keys = array_filter(array_keys($labels['default']), function($item) use ($keyPrefix) {
			return substr($item, 0, strlen($keyPrefix)) === $keyPrefix;
		});

		$messages = array();
		foreach ($keys as $key) {
			$messages[$key] = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($key, 'sphinx');
		}

		$json = json_encode($messages);
		$out = <<<JS
$(document).ready(function() {
	$jsDictionnary = $json;
});
JS;

		return $out;
	}

}
