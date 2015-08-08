<?php
namespace Causal\Sphinx\Utility;

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
class Configuration
{

    /**
     * Loads a conf.py configuration file and returns an array with
     * available properties.
     *
     * @param string $configurationFilename Absolute filename to the configuration file conf.py
     * @return array
     */
    static public function load($configurationFilename)
    {
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
            $properties['t3sphinx'] = true;
        } else {
            $properties['t3sphinx'] = false;
        }

        return $properties;
    }

}
