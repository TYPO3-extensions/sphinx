<?php
namespace Causal\Sphinx\ViewHelpers\IncludeJs;

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
 * Includes a given version from jQuery
 *
 * @category    ViewHelpers\IncludeJs
 * @package     tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class JQueryViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Includes a jQuery. If version is empty, includes the latest version available locally,
	 * otherwise if version is a 3-digit value, then include the given version, otherwise
	 * version should be a 2-digit value.
	 *
	 * @param string $version
	 * @return string
	 */
	public function render($version = '') {
		$isEmpty = empty($version);
		$isThreeDigits = !$isEmpty && preg_match('/^\d+\.\d+\.\d+$/', $version);
		$isTwoDigits = !$isEmpty && !$isThreeDigits && preg_match('/^\d+\.\d+$/', $version);

		if (!$isThreeDigits) {
			$files = \TYPO3\CMS\Core\Utility\GeneralUtility::getFilesInDir(PATH_typo3 . 'contrib/jquery');
			foreach ($files as $file) {
				if (preg_match('/^jquery-([0-9.]+).min.js$/', $file, $matches)) {
					if ($isTwoDigits) {
						if (version_compare($matches[1], $version, '>=')
							&& version_compare($matches[1], $version . '.99', '<=')) {

							$version = $matches[1];
							break;
						}
					} elseif (empty($version) || version_compare($version, $matches[1], '<')) {
						$version = $matches[1];
					}
				}
			}
		}

		$jqueryFilename = 'jquery-' . $version . '.min.js';
		if (!is_file(PATH_typo3 . 'contrib/jquery/' . $jqueryFilename)) {
			// Load from Google's CDN
			$scheme = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SSL') ? 'https' : 'http';
			if ($isTwoDigits && version_compare($version, '1.8', '<=')) {
				$src = $scheme . '://ajax.googleapis.com/ajax/libs/jquery/' . $version . '/jquery.min.js';
			} else {
				// No way to retrieve latest patch version of jQuery since jQuery 1.9 :(
				$src = $scheme . '://ajax.googleapis.com/ajax/libs/jquery/' . $version . '.0/jquery.min.js';
			}
		} else {
			$src = 'contrib/jquery/' . $jqueryFilename;
		}

		return '<script type="text/javascript" src="' . $src . '"></script>';
	}

}
