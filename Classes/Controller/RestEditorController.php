<?php
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

namespace Causal\Sphinx\Controller;

/**
 * ReStructuredText Editor for the 'sphinx' extension.
 *
 * @category    Backend Module
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class RestEditorController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * Edit action.
	 *
	 * @param string $extension
	 * @param string $document
	 * @return void
	 */
	protected function editAction($extension, $document) {
		// TODO: security check for the document to be loaded
		$path = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extension) . 'Documentation/';
		$filename = $path . ($document ? substr($document, 0, -1) : 'Index') . '.rst';

		$this->view->assign('extension', $extension);
		$this->view->assign('document', $document);
		$this->view->assign('content', file_get_contents($filename));
	}

	/**
	 * Saves the content.
	 *
	 * @param string $extension
	 * @param string $document
	 * @param string $contents
	 * @param boolean $compile
	 * @return string
	 */
	protected function saveAction($extension, $document, $contents, $compile = FALSE) {
		// TODO: security check for the document to be written
		$path = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extension) . 'Documentation/';
		$filename = $path . ($document ? substr($document, 0, -1) : 'Index') . '.rst';

		if (\TYPO3\CMS\Core\Utility\GeneralUtility::writeFile($filename, $contents)) {
			if ($compile) {
				\Causal\Sphinx\Utility\GeneralUtility::generateDocumentation($extension, 'json', TRUE);
			}
		}

		return 'OK';
	}

}

?>