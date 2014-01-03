<?php
namespace Causal\Sphinx\Utility;

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

/**
 * conf.py utility.
 *
 * @category    Utility
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Configuration {

	/**
	 * Loads a conf.py configuration file and returns an array with
	 * available properties.
	 *
	 * @param string $configurationFilename Absolute filename to the configuration file conf.py
	 * @return array
	 */
	static public function load($configurationFilename) {
		$contents = file_get_contents($configurationFilename);
		$properties = array();

		preg_replace_callback(
			'/^\s*([^#].*?)\s*=\s*u?\'(.*)\'/m',
			function ($matches) use (&$properties) {
				$properties[$matches[1]] = stripcslashes($matches[2]);
			},
			$contents
		);

		// Detect if theme t3sphinx from project TYPO3 Documentation Team's project
		// ReST Tools is being used
		// @see http://forge.typo3.org/issues/48311
		if (preg_match('/^\s*import\s+t3sphinx\s*$/m', $contents)) {
			$properties['t3sphinx'] = TRUE;
		} else {
			$properties['t3sphinx'] = FALSE;
		}

		return $properties;
	}

}
