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

namespace Causal\Sphinx\ViewHelpers\Form;

use Causal\Sphinx\Utility\MiscUtility;

/**
 * Extends the EXT:fluid's select VH to support onchange attribute.
 *
 * @category    ViewHelpers\Form
 * @package     tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class SelectViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\SelectViewHelper
{

    /** @var string */
    protected $extKey = 'sphinx';

    /**
     * Initializes arguments.
     *
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerTagAttribute('onchange', 'string', 'Javascript for the onchange event');
        $this->registerArgument('groupOptions', 'boolean', 'Whether options should be grouped by 1st-dimension key');
    }

    /**
     * Gets the select options.
     *
     * @return array an associative array of options, key will be the value of the option tag
     * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
     */
    protected function getOptions()
    {
        if (!is_array($this->arguments['options']) && !$this->arguments['options'] instanceof \Traversable) {
            return array();
        }
        $options = array();
        $optionsArgument = $this->arguments['options'];
        foreach ($optionsArgument as $key => $value) {
            // Additional test for $this->hasArgument below is what is different with parent method
            if (is_object($value) || (is_array($value) && $this->hasArgument('optionValueField'))) {
                if ($this->hasArgument('optionValueField')) {
                    $key = \TYPO3\CMS\Extbase\Reflection\ObjectAccess::getPropertyPath($value, $this->arguments['optionValueField']);
                    if (is_object($key)) {
                        if (method_exists($key, '__toString')) {
                            $key = (string)$key;
                        } else {
                            throw new \TYPO3\CMS\Fluid\Core\ViewHelper\Exception('Identifying value for object of class "' . get_class($value) . '" was an object.', 1247827428);
                        }
                    }
                    // TODO: use $this->persistenceManager->isNewObject() once it is implemented
                } elseif ($this->persistenceManager->getIdentifierByObject($value) !== null) {
                    $key = $this->persistenceManager->getIdentifierByObject($value);
                } elseif (method_exists($value, '__toString')) {
                    $key = (string)$value;
                } else {
                    throw new \TYPO3\CMS\Fluid\Core\ViewHelper\Exception('No identifying value for object of class "' . get_class($value) . '" found.', 1247826696);
                }
                if ($this->hasArgument('optionLabelField')) {
                    $value = \TYPO3\CMS\Extbase\Reflection\ObjectAccess::getPropertyPath($value, $this->arguments['optionLabelField']);
                    if (is_object($value)) {
                        if (method_exists($value, '__toString')) {
                            $value = (string)$value;
                        } else {
                            throw new \TYPO3\CMS\Fluid\Core\ViewHelper\Exception('Label value for object of class "' . get_class($value) . '" was an object without a __toString() method.', 1247827553);
                        }
                    }
                } elseif (method_exists($value, '__toString')) {
                    $value = (string)$value;
                    // TODO: use $this->persistenceManager->isNewObject() once it is implemented
                } elseif ($this->persistenceManager->getIdentifierByObject($value) !== null) {
                    $value = $this->persistenceManager->getIdentifierByObject($value);
                }
            }
            $options[$key] = $value;
        }
        if ($this->arguments['sortByOptionLabel']) {
            asort($options, SORT_LOCALE_STRING);
        }
        return $options;
    }

    /**
     * Renders the option tags.
     *
     * @param array $options the options for the form.
     * @return string rendered tags.
     */
    protected function renderOptionTags($options)
    {
        $output = '';
        if ($this->hasArgument('prependOptionLabel')) {
            $value = $this->hasArgument('prependOptionValue') ? $this->arguments['prependOptionValue'] : '';
            $label = $this->arguments['prependOptionLabel'];
            $icon = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($this->extKey) .
                'Resources/Public/Images/dashboard.png';
            $output .= $this->renderOptionTag($value, $label, $icon, false) . LF;
        }
        if ($this->arguments['groupOptions']) {
            foreach ($options as $group => $valueLabel) {
                $output .= '<optgroup label="' . htmlspecialchars($group) . '">';
                foreach ($valueLabel as $value => $label) {
                    if (substr($value, 0, 4) === 'EXT:') {
                        list($extensionKey, $_) = explode('.', substr($value, 4), 2);
                        $lengthSuffixReadme = strlen(\Causal\Sphinx\Domain\Repository\ExtensionRepository::SUFFIX_README);
                        if (substr($extensionKey, -$lengthSuffixReadme) === \Causal\Sphinx\Domain\Repository\ExtensionRepository::SUFFIX_README) {
                            $extensionKey = substr($extensionKey, 0, -$lengthSuffixReadme);
                        }
                        $extPath = MiscUtility::extPath($extensionKey);
                        $extRelPath = '../' . MiscUtility::extRelPath($extensionKey);
                        $icon = $extRelPath . \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getExtensionIcon($extPath);
                        $hiresIcon = 'ext_icon@2x.png';
                        if (is_file($extPath . $hiresIcon)) {
                            $icon = $extRelPath . $hiresIcon;
                        }
                    } else {
                        $icon = '../' . MiscUtility::extRelPath($this->extKey) .
                            'Resources/Public/Images/default_icon@2x.png';
                    }
                    $isSelected = $this->isSelected($value);
                    $output .= $this->renderCustomOptionTag($value, $label, $icon, $isSelected) . LF;
                }
                $output .= '</optgroup>';
            }
        } else {
            foreach ($options as $value => $label) {
                $isSelected = $this->isSelected($value);
                $output .= $this->renderCustomOptionTag($value, $label, '', $isSelected) . LF;
            }
        }
        return $output;
    }

    /**
     * Renders one option tag.
     *
     * @param string $value value attribute of the option tag (will be escaped)
     * @param string $label content of the option tag (will be escaped)
     * @param string $icon icon to show
     * @param boolean $isSelected specifies wheter or not to add selected attribute
     * @return string the rendered option tag
     */
    protected function renderCustomOptionTag($value, $label, $icon, $isSelected)
    {
        $output = '<option value="' . htmlspecialchars($value) . '"';
        if ($icon) {
            $output .= ' data-iconurl="' . $icon . '"';
        }
        if ($isSelected) {
            $output .= ' selected="selected"';
        }
        $output .= '>' . htmlspecialchars($label) . '</option>';
        return $output;
    }

}
