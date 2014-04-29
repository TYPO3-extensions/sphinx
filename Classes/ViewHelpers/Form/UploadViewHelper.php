<?php
namespace Causal\Sphinx\ViewHelpers\Form;

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

use Causal\Sphinx\Utility\MiscUtility;

/**
 * Extends the EXT:fluid's upload VH to support multiple files.
 *
 * @category    ViewHelpers\Form
 * @package     tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class UploadViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper {

	/**
	 * @var string
	 */
	protected $tagName = 'input';

	/**
	 * Initialize the arguments.
	 *
	 * @return void
	 * @api
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerTagAttribute('multiple', 'string', 'Specifies that the input element should allow multiple files');
		$this->registerTagAttribute('disabled', 'string', 'Specifies that the input element should be disabled when the page loads');
		$this->registerArgument('errorClass', 'string', 'CSS class to set if there are errors for this view helper', FALSE, 'f3-form-error');
		$this->registerUniversalTagAttributes();
	}

	/**
	 * Renders the upload field.
	 *
	 * @return string
	 * @api
	 */
	public function render() {
		$name = $this->getName();
		$hasMultiple = $this->tag->getAttribute('multiple') !== NULL;
		$allowedFields = array('name', 'type', 'tmp_name', 'error', 'size');
		foreach ($allowedFields as $fieldName) {
			$this->registerFieldNameForFormTokenGeneration($name . '[' . $fieldName . ']' . ($hasMultiple ? '[]' : ''));
		}
		$this->tag->addAttribute('type', 'file');
		$this->tag->addAttribute('name', $name . ($hasMultiple ? '[]' : ''));
		$this->setErrorClassAttribute();
		return $this->tag->render();
	}

}
