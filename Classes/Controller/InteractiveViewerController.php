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

		$masterToc = $this->sphinxReader->getMasterTableOfContents(
			array($this, 'getLink')
		);
		$data = $masterToc ? \Tx_Restdoc_Utility_Helper::getMenuData(\Tx_Restdoc_Utility_Helper::xmlstr_to_array($masterToc)) : array();
		$this->markActiveAndCurrentEntries($data, $document);
		$masterToc = $this->createMasterMenu($data);

		$toc = $this->sphinxReader->getTableOfContents(
			array($this, 'getLink')
		);

		$content = $this->sphinxReader->getBody(
			array($this, 'getLink'),
			array($this, 'processImage')
		);

		$this->view->assign('masterToc', $masterToc);
		$this->view->assign('toc', $toc);
		$this->view->assign('content', $content);
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
	 * Marks menu entries as ACTIVE or CURRENT.
	 *
	 * @param array &$data
	 * @param string $currentDocument
	 * @return boolean
	 * @see \Tx_Restdoc_Utility_Helper::markActiveAndCurrentEntries()
	 */
	protected function markActiveAndCurrentEntries(array &$data, $currentDocument) {
		$hasCurrent = FALSE;

		foreach ($data as &$menuEntry) {
			$link = urldecode($menuEntry['_OVERRIDE_HREF']);
			if (preg_match('/[?&]tx_sphinx_help_sphinxdocumentation\[document\]=([^&#]+)/', $link, $matches)) {
				$link = $matches[1];
				if ($link === $currentDocument) {
					$hasCurrent = TRUE;
					$menuEntry['ITEM_STATE'] = 'CUR';
				}
			}
			if (isset($menuEntry['_SUB_MENU'])) {
				$hasChildCurrent = $this->markActiveAndCurrentEntries($menuEntry['_SUB_MENU'], $currentDocument);
				if ($hasChildCurrent) {
					$menuEntry['ITEM_STATE'] = 'ACT';
				}
			}
		}

		return $hasCurrent;
	}

	/**
	 * Creates a master menu compatible with the interactive design.
	 *
	 * @param array $data
	 * @param integer $level
	 * @return array
	 */
	protected function createMasterMenu(array $data, $level = 1) {
		$menu = array();
		if ($level == 1) {
			$menu[] = '<ul id="nav-aside" class="current cur">';
			$wrapTitle = '%s';
		} else {
			$menu[] = '<ul class="nav-aside-lvl' . $level . '">';
			$wrapTitle = '<span>%s</span>';
		}

		foreach ($data as $menuEntry) {
			if (isset($menuEntry['ITEM_STATE']) && $menuEntry['ITEM_STATE'] === 'CUR') {
				$currentClass = ' current cur';
			} else {
				$currentClass = '';
			}

			$menu[] = '<li class="toctree-l' . $level . $currentClass . ' nav-aside-lvl' . $level . '">';
			$menu[] = '<a href="' . str_replace('&', '&amp;', $menuEntry['_OVERRIDE_HREF']) . '" class="nav-aside-lvl' . $level . $currentClass . '">' . sprintf($wrapTitle, htmlspecialchars($menuEntry['title'])) . '</a>';

			$generateSubMenu = $level == 1;
			$generateSubMenu |= isset($menuEntry['ITEM_STATE']) && ($menuEntry['ITEM_STATE'] === 'CUR' || $menuEntry['ITEM_STATE'] === 'ACT');
			$generateSubMenu &= isset($menuEntry['_SUB_MENU']);

			if ($generateSubMenu) {
				$menu[] = $this->createMasterMenu($menuEntry['_SUB_MENU'], $level + 1);
			}
			$menu[] = '</li>';
		}

		$menu[] = '</ul>';

		return implode('', $menu);
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