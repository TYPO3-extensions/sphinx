<?php
namespace Causal\Sphinx\Controller;

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
 * ReStructuredText Editor for the 'sphinx' extension.
 *
 * @category    Backend Module
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class RestEditorController extends AbstractActionController {

	/**
	 * Edit action.
	 *
	 * @param string $reference Reference of a documentation
	 * @param string $document The document
	 * @return void
	 * @throws \RuntimeException
	 */
	protected function editAction($reference, $document) {
		$parts = $this->parseReferenceDocument($reference, $document);
		$contents = file_get_contents($parts['filename']);

		$this->view->assign('reference', $reference);
		$this->view->assign('document', $document);
		$this->view->assign('contents', $contents);

		$buttons = $this->getButtons();
		$this->view->assign('buttons', $buttons);
	}

	/**
	 * Saves the contents and recompiles the whole documentation if needed.
	 *
	 * @param string $reference Reference of a documentation
	 * @param string $document The document
	 * @param string $contents New contents to be saved
	 * @param boolean $compile
	 * @return void
	 */
	protected function saveAction($reference, $document, $contents, $compile = FALSE) {
		$response = array();
		try {
			$parts = $this->parseReferenceDocument($reference, $document);

			$success = \TYPO3\CMS\Core\Utility\GeneralUtility::writeFile($parts['filename'], $contents);
			if (!$success) {
				throw new \RuntimeException('File could not be written: ' . $parts['filename'], 1370011487);
			}

			if ($compile) {
				$layout = 'json';
				$force = TRUE;
				$outputFilename = NULL;

				switch ($parts['type']) {
					case 'EXT':
						$outputFilename = \Causal\Sphinx\Utility\GeneralUtility::generateDocumentation($parts['extensionKey'], $layout, $force, $parts['locale']);
						break;
					case 'USER':
						$outputFilename = NULL;
						$this->signalSlotDispatcher->dispatch(
							'Causal\\Sphinx\\Controller\\DocumentationController',
							'renderUserDocumentation',
							array(
								'identifier' => $parts['identifier'],
								'layout' => $layout,
								'force' => $force,
								'documentationUrl' => &$outputFilename,
							)
						);
						break;
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
	 * Parses a reference and a document and returns the corresponding filename,
	 * the type of reference, its identifier, the extension key (if available)
	 * and the locale (if available).
	 *
	 * @param string $reference
	 * @param string $document
	 * @return array
	 * @throws \RuntimeException
	 */
	protected function parseReferenceDocument($reference, $document) {
		$extensionKey = NULL;
		$locale = NULL;

		list($type, $identifier) = explode(':', $reference, 2);
		switch ($type) {
			case 'EXT':
				list($extensionKey, $locale) = explode('.', $identifier, 2);
				$filename = $this->getFilename($extensionKey, $document, $locale);
				break;
			case 'USER':
				$filename = NULL;
				$this->signalSlotDispatcher->dispatch(
					__CLASS__,
					'retrieveRestFilename',
					array(
						'identifier' => $identifier,
						'document' => $document,
						'filename' => &$filename,
					)
				);
				if ($filename === NULL) {
					throw new \RuntimeException('No slot found to retrieve filename with identifier "' . $identifier . '"', 1371418203);
				}
				break;
			default:
				throw new \RuntimeException('Unknown reference "' . $reference . '"', 1371163472);
		}

		return array(
			'filename'     => $filename,
			'type'         => $type,
			'identifier'   => $identifier,
			'extensionKey' => $extensionKey,
			'locale'       => $locale
		);
	}

	/**
	 * Returns the ReST filename corresponding to a given document.
	 *
	 * @param string $extensionKey The TYPO3 extension key
	 * @param string $document The document
	 * @param string $locale The locale to use
	 * @return string
	 * @throws \RuntimeException
	 */
	protected function getFilename($extensionKey, $document, $locale) {
		if (empty($locale)) {
			$documentationType = \Causal\Sphinx\Utility\GeneralUtility::getDocumentationType($extensionKey);
		} else {
			$documentationType = \Causal\Sphinx\Utility\GeneralUtility::getLocalizedDocumentationType($extensionKey, $locale);
		}
		switch ($documentationType) {
			case \Causal\Sphinx\Utility\GeneralUtility::DOCUMENTATION_TYPE_SPHINX:
				$path = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extensionKey);
				if (empty($locale)) {
					$path .= 'Documentation/';
				} else {
					$localizationDirectories = \Causal\Sphinx\Utility\GeneralUtility::getLocalizationDirectories($extensionKey);
					$path .= $localizationDirectories[$locale]['directory'] . '/';
				}
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

	/**
	 * Returns the toolbar buttons.
	 *
	 * @return string
	 */
	protected function getButtons() {
		$buttons = array();

		$buttons[] = $this->createToolbarButton(
			'#',
			'Close document',
			't3-icon-actions-document t3-icon-document-close',
			'getContentIframe().closeEditor()'
		);
		$buttons[] = '&nbsp;';

		$buttons[] = $this->createToolbarButton(
			'#',
			'Save document',
			't3-icon-actions-document t3-icon-document-save',
			'getContentIframe().save()'
		);
		$buttons[] = $this->createToolbarButton(
			'#',
			'Save and close document',
			't3-icon-actions-document t3-icon-document-save-close',
			'getContentIframe().saveAndClose()'
		);

		$buttons[] = '<div style="float:right">';
		$buttons[] = '<input type="checkbox" id="tx-sphinx-showinvisibles" onclick="getContentIframe().editor.setShowInvisibles(this.checked)" value="1" />' .
			'<label for="tx-sphinx-showinvisibles">' .
			$this->translate('showInvisibles') . '</label>';
		$buttons[] = '</div>';

		return implode(' ', $buttons);
	}

}

?>