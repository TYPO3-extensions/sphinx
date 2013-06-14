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
	 * @param string $reference
	 * @param string $document
	 * @return void
	 * @throws \RuntimeException
	 */
	protected function editAction($reference, $document) {
		list($type, $identifier) = explode(':', $reference, 2);
		switch ($type) {
			case 'EXT':
				$extensionKey = $identifier;
				$filename = $this->getFilename($extensionKey, $document);
				break;
			default:
				throw new \RuntimeException('Unknown reference "' . $reference . '"', 1371163472);
		}
		$contents = file_get_contents($filename);

		$this->view->assign('reference', $reference);
		$this->view->assign('document', $document);
		$this->view->assign('contents', $contents);
	}

	/**
	 * Saves the contents and recompiles the whole documentation if needed.
	 *
	 * @param string $reference
	 * @param string $document
	 * @param string $contents
	 * @param boolean $compile
	 * @return void
	 */
	protected function saveAction($reference, $document, $contents, $compile = FALSE) {
		$response = array();
		try {
			list($type, $identifier) = explode(':', $reference, 2);
			switch ($type) {
				case 'EXT':
					$extensionKey = $identifier;
					$filename = $this->getFilename($extensionKey, $document);
					break;
				default:
					throw new \RuntimeException('Unknown reference "' . $reference . '"', 1371163472);
			}

			$success = \TYPO3\CMS\Core\Utility\GeneralUtility::writeFile($filename, $contents);
			if (!$success) {
				throw new \RuntimeException('File could not be written: ' . $filename, 1370011487);
			}

			if ($compile) {
				switch ($type) {
					case 'EXT':
						$outputFilename = \Causal\Sphinx\Utility\GeneralUtility::generateDocumentation($extensionKey, 'json', TRUE);
				}
				if (substr($outputFilename, -4) === '.log') {
					throw new \RuntimeException('Could not compile documentation', 1370011537);
				}
			}

			$response['status'] = 'success';
		} catch (\RuntimeException $e) {
			$response['status'] = 'error';
			$response['statusText'] = 'Error ' . $e->getCode() . ': ' . $e->getMessage();
		}

		header('Content-type: application/json');
		echo json_encode($response);
		exit;
	}

	/**
	 * Returns the ReST filename corresponding to a given document.
	 *
	 * @param string $extensionKey
	 * @param string $document
	 * @return string
	 * @throws \RuntimeException
	 */
	protected function getFilename($extensionKey, $document) {
		$documentationType = \Causal\Sphinx\Utility\GeneralUtility::getDocumentationType($extensionKey);
		switch ($documentationType) {
			case \Causal\Sphinx\Utility\GeneralUtility::DOCUMENTATION_TYPE_STANDARD:
				$path = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extensionKey) . 'Documentation/';
				$filename = $path . ($document ? substr($document, 0, -1) : 'Index') . '.rst';
				break;
			case \Causal\Sphinx\Utility\GeneralUtility::DOCUMENTATION_TYPE_README:
				$path = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extensionKey);
				$filename = $path . 'README.rst';
				break;
			default:
				throw new \RuntimeException('Unsupported documentation type for extension "' . $extensionKey . '"', 1371117564);
		}

		// Security check
		$path = realpath($path);
		$filename = realpath($filename);
		if (substr($filename, 0, strlen($path)) !== $path) {
			throw new \RuntimeException('Security notice: attempted to access a file outside of extension "' . $extensionKey . '"', 1370011326);
		}

		return $filename;
	}

}

?>