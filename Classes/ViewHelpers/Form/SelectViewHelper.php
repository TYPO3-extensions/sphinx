<?php
namespace Causal\Sphinx\ViewHelpers\Form;

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
 * Extends the EXT:fluid's select VH to support onchange attribute.
 *
 * @category    ViewHelpers\Form
 * @package     tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class SelectViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\SelectViewHelper {

	/** @var string */
	protected $extKey = 'sphinx';

	/**
	 * Initializes arguments.
	 *
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerTagAttribute('onchange', 'string', 'Javascript for the onchange event');
		$this->registerArgument('groupOptions', 'boolean', 'Whether options should be grouped by 1st-dimension key');

		if (version_compare(TYPO3_version, '6.1.0', '<')) {
			$this->registerArgument('prependOptionLabel', 'string', 'If specified, will provide an option at first position with the specified label.');
			$this->registerArgument('prependOptionValue', 'string', 'If specified, will provide an option at first position with the specified value.');
		}
	}

	/**
	 * Renders the option tags.
	 *
	 * @param array $options the options for the form.
	 * @return string rendered tags.
	 */
	protected function renderOptionTags($options) {
		$output = '';
		if ($this->hasArgument('prependOptionLabel')) {
			$value = $this->hasArgument('prependOptionValue') ? $this->arguments['prependOptionValue'] : '';
			$label = $this->arguments['prependOptionLabel'];
			$icon = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($this->extKey) .
				'Resources/Public/Images/dashboard.png';
			$output .= $this->renderOptionTag($value, $label, $icon, FALSE) . LF;
		}
		if ($this->arguments['groupOptions']) {
			foreach ($options as $group => $valueLabel) {
				$output .= '<optgroup label="' . htmlspecialchars($group) . '">';
				foreach ($valueLabel as $value => $label) {
					if (substr($value, 0, 4) === 'EXT:') {
						list($extensionKey, $_) = explode('.', substr($value, 4), 2);
						$extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extensionKey);
						$extRelPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($extensionKey);
						$icon = $extRelPath . \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getExtensionIcon($extPath);
						$hiresIcon = 'ext_icon@2x.png';
						if (is_file($extPath . $hiresIcon)) {
							$icon = $extRelPath . $hiresIcon;
						}
					} else {
						$icon = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($this->extKey) .
							'Resources/Public/Images/default_icon@2x.png';
					}
					$isSelected = $this->isSelected($value);
					$output .= $this->renderOptionTag($value, $label, $icon, $isSelected) . LF;
				}
				$output .= '</optgroup>';
			}
		} else {
			foreach ($options as $value => $label) {
				$isSelected = $this->isSelected($value);
				$output .= $this->renderOptionTag($value, $label, '', $isSelected) . LF;
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
	protected function renderOptionTag($value, $label, $icon, $isSelected) {
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
