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
 * Interactive Documentation Viewer for the 'sphinx' extension.
 *
 * @category    Backend Module
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class InteractiveViewerController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/** @var \Tx_Restdoc_Reader_SphinxJson */
	protected $sphinxReader;

	/** @var string */
	protected $extension;

	///**
	// * @param \Tx_Restdoc_Reader_SphinxJson $sphinxReader
	// * @return void
	// */
	//public function injectSphinxReader(\Tx_Restdoc_Reader_SphinxJson $sphinxReader) {
	//	$this->sphinxReader = $sphinxReader;
	//}

	/**
	 * Unfortunately cannot use inject method as EXT:restdoc may not be loaded.
	 *
	 * @return void
	 */
	protected function initializeAction() {
		if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('restdoc')) {
			$this->sphinxReader = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Tx_Restdoc_Reader_SphinxJson');
			$this->sphinxReader
				->setKeepPermanentLinks(FALSE)
				->setDefaultFile('Index')
				->enableDefaultDocumentFallback();
		}
	}

	/**
	 * Main action.
	 *
	 * @param string $extension
	 * @param string $document
	 * @return void
	 */
	protected function renderAction($extension, $document = '') {
		$this->checkExtensionRestdoc();

		$this->extension = $extension;

		$this->sphinxReader
			->setPath(PATH_site . 'typo3conf/Documentation/' . $extension . '/json')
			->setDocument($document ?: $this->sphinxReader->getDefaultFile() . '/')
			->load();

		/** @var \Causal\Sphinx\Domain\Model\Documentation $documentation */
		$documentation = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Causal\Sphinx\Domain\Model\Documentation', $this->sphinxReader);
		$documentation->setCallbackLinks(array($this, 'getLink'));
		$documentation->setCallbackImages(array($this, 'processImage'));

		$this->view->assign('documentation', $documentation);
		$this->view->assign('extension', \Causal\Sphinx\Utility\GeneralUtility::getExtensionMetaData($extension));
		$this->view->assign('document', $document);
	}

	/**
	 * Missing EXT:restdoc action.
	 *
	 * @return void
	 */
	protected function missingRestdocAction() {
		// Nothing to do
	}

	/**
	 * Outdated EXT:restdoc action.
	 *
	 * @return void
	 */
	protected function outdatedRestdocAction() {
		// Nothing to do
	}

	/**
	 * Checks that EXT:restdoc is properly available.
	 *
	 * @return void
	 */
	protected function checkExtensionRestdoc() {
		if (!\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('restdoc')) {
			$this->forward('missingRestdoc');
		}
		$restdocVersion = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getExtensionVersion('restdoc');
		// Removes -dev -alpha -beta -RC states from a version number
		// and replaces them by .0
		if (stripos($restdocVersion, '-dev') || stripos($restdocVersion, '-alpha') || stripos($restdocVersion, '-beta') || stripos($restdocVersion, '-RC')) {
			// Find the last occurence of "-" and replace that part with a ".0"
			$restdocVersion = substr($restdocVersion, 0, strrpos($restdocVersion, '-')) . '.0';
		}

		$metadata = \Causal\Sphinx\Utility\GeneralUtility::getExtensionMetaData($this->request->getControllerExtensionKey());
		list($minVersion, $maxVersion) = explode('-', $metadata['constraints']['suggests']['restdoc']);

		if (version_compare($restdocVersion, $minVersion, '<')) {
			$this->forward('outdatedRestdoc');
		}
	}

	/**
	 * Generates a link to navigate within a reST documentation project.
	 *
	 * @param string $document Target document
	 * @param boolean $absolute Whether absolute URI should be generated
	 * @param integer $rootPage UID of the page showing the documentation
	 * @return string
	 * @private This method is made public to be accessible from a lambda-function scope
	 */
	public function getLink($document, $absolute = FALSE, $rootPage = 0) {
		$anchor = '';
		if ($document !== '') {
			if (($pos = strrpos($document, '#')) !== FALSE) {
				$anchor = substr($document, $pos + 1);
				$document = substr($document, 0, $pos);
			}
		}
		$link = $this->uriBuilder->uriFor(
			'render',
			array(
				'extension' => $this->extension,
				'document' => $document
			)
		);
		if ($anchor !== '') {
			$link .= '#' . $anchor;
		} /*elseif (\TYPO3\CMS\Core\Utility\GeneralUtility::isFirstPartOfStr($document, '_sources/')) {
			$link = '../typo3conf/Documentation/' . $this->extension . '/json/' . $document;
		} */
		return $link;
	}

	/**
	 * Processes an image.
	 *
	 * @param array $data
	 * @return string
	 * @private This method is made public to be accessible from a lambda-function scope
	 */
	public function processImage(array $data) {
		return sprintf(
			'<img src="../%s" alt="%s" style="%s" />',
			htmlspecialchars($data['src']),
			htmlspecialchars($data['alt']),
			htmlspecialchars($data['style'])
		);
	}

}

?>