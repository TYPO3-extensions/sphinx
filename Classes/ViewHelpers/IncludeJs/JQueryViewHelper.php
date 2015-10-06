<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with TYPO3 source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Causal\Sphinx\ViewHelpers\IncludeJs;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Includes a given version from jQuery
 *
 * @category    ViewHelpers\IncludeJs
 * @package     tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class JQueryViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * Includes a jQuery. If version is empty, includes the latest version available locally,
     * otherwise if version is a 3-digit value, then include the given version, otherwise
     * version should be a 2-digit value.
     *
     * @param string $version
     * @return string
     */
    public function render($version = '')
    {
        $isEmpty = empty($version);
        $isThreeDigits = !$isEmpty && preg_match('/^\d+\.\d+\.\d+$/', $version);
        $isTwoDigits = !$isEmpty && !$isThreeDigits && preg_match('/^\d+\.\d+$/', $version);
        $origVersion = $version;

        if (!$isThreeDigits) {
            $files = GeneralUtility::getFilesInDir(PATH_typo3 . 'contrib/jquery');
            foreach ($files as $file) {
                if (preg_match('/^jquery-([0-9.]+).min.js$/', $file, $matches)) {
                    if ($isTwoDigits) {
                        if (version_compare($matches[1], $version, '>=')
                            && version_compare($matches[1], $origVersion . '.99', '<=')
                        ) {

                            $version = $matches[1];
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
            $scheme = GeneralUtility::getIndpEnv('TYPO3_SSL') ? 'https' : 'http';
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
