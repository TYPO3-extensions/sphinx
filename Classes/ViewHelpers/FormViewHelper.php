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
 * Extends the EXT:fluid's form VH to support target attribute.
 *
 * @category    ViewHelpers
 * @package     tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class FormViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\FormViewHelper {

	/**
	 * Initialize arguments.
	 *
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerTagAttribute('target', 'string', 'Target of form');
		parent::initializeArguments();
	}

}
