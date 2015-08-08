<?php
namespace Causal\Sphinx\ViewHelpers;

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
 * Includes localized messages.
 *
 * @category    ViewHelpers
 * @package     tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class IncludeMessagesViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * Renders the JS snippet.
     *
     * @param string $keyPrefix
     * @param string $jsDictionnary
     * @return string
     */
    public function render($keyPrefix, $jsDictionnary)
    {
        $llFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('sphinx') . 'Resources/Private/Language/locallang.xlf';
        $labels = $GLOBALS['LANG']->includeLLFile($llFile, FALSE);
        $keys = array_filter(array_keys($labels['default']), function ($item) use ($keyPrefix) {
            return substr($item, 0, strlen($keyPrefix)) === $keyPrefix;
        });

        $messages = array();
        foreach ($keys as $key) {
            $messages[$key] = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($key, 'sphinx');
        }

        $json = json_encode($messages);
        $out = <<<JS
$(document).ready(function () {
    $jsDictionnary = $json;
});
JS;

        return $out;
    }

}
