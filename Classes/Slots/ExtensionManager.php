<?php
namespace Causal\Sphinx\Slots;

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
 * Slot implementation to modify the list of actions in Extension Manager.
 *
 * @category    Slots
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class ExtensionManager {

	/**
	 * Extends the list of actions for EXT:sphinx to change the
	 * icon of "ext_update.php"
	 *
	 * @param array $extension
	 * @param array $actions
	 */
	public function processActions(array $extension, array &$actions) {
		if ($extension['key'] === 'sphinx') {
			$title = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('em.downloadSphinx', $extension['key']);
			$actions[1] = preg_replace(
				'#<a href="([^"]+)" title="[^"]+">.+</a>#',
				'<a href="\1" title="' . htmlspecialchars($title) . '">' .
					\TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('extensions-sphinx-download') .
				'</a>',
				$actions[1]
			);
		}
	}

}
