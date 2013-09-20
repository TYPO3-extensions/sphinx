<?php
namespace Causal\Sphinx\Controller;

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
 * Abstract multi action controller.
 *
 * @category    Controller
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
abstract class AbstractActionController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * Needed in TYPO3 6.0.0-6.0.99
	 *
	 * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
	 * @inject
	 */
	protected $signalSlotDispatcher;

	/**
	 * Injects settings.
	 *
	 * @return void
	 */
	protected function injectSettings() {
		$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->request->getControllerExtensionKey()]);
		if (!is_array($configuration)) {
			$configuration = array();
		}
		$this->settings = \TYPO3\CMS\Core\Utility\GeneralUtility::array_merge($configuration, $this->settings);
	}

	/**
	 * Returns the localized label of a given key.
	 *
	 * @param string $key The label key
	 * @return string Localized label
	 */
	protected function translate($key) {
		return \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($key, $this->request->getControllerExtensionKey());
	}

	/**
	 * Returns the Backend user.
	 *
	 * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected function getBackendUser() {
		return $GLOBALS['BE_USER'];
	}

	/**
	 * Creates a toolbar button.
	 *
	 * @param string $link
	 * @param string $title
	 * @param string $iconClasses
	 * @param string $onClick
	 * @return string
	 */
	protected function createToolbarButton($link, $title, $iconClasses, $onClick = '') {
		$button =
			'<a href="' . htmlspecialchars($link) . '"' .
			($onClick ? ' onclick="' . $onClick . ';return false;"' : '') .
			' title="' . htmlspecialchars($title) . '"' .
				' target="tx-sphinx-documentation-content">' .
				'<span class="t3-icon ' . $iconClasses . '">&nbsp;</span>' .
			'</a>';
		// Replacement of single quotes to be compatible with the dynamic update of the toolbar
		return str_replace('\'', '\\\'', $button);
	}

}

?>