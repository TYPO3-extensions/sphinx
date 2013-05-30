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

namespace Causal\Sphinx\Domain\Model;

/**
 * Domain model for a Documentation.
 *
 * @category    Domain\Model
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Documentation {

	/**
	 * @var \Tx_Restdoc_Reader_SphinxJson
	 */
	protected $sphinxReader;

	/** @var callback */
	protected $callbackLinks;

	/** @var callback */
	protected $callbackImages;

	/**
	 * Default constructor.
	 *
	 * @param \Tx_Restdoc_Reader_SphinxJson $sphinxReader
	 */
	public function __construct(\Tx_Restdoc_Reader_SphinxJson $sphinxReader) {
		$this->sphinxReader = $sphinxReader;
	}

	/**
	 * @param callback $callbackLinks Callback to generate Links in current context
	 * @return \Causal\Sphinx\Domain\Model\Documentation
	 */
	public function setCallbackLinks($callbackLinks) {
		$this->callbackLinks = $callbackLinks;
		return $this;
	}

	/**
	 * @param callback $callbackImages function to process images in current context
	 * @return \Causal\Sphinx\Domain\Model\Documentation
	 */
	public function setCallbackImages($callbackImages) {
		$this->callbackImages = $callbackImages;
		return $this;
	}

	/**
	 * Returns the master table of contents.
	 *
	 * @return string
	 */
	public function getMasterTableOfContents() {
		$masterToc = $this->sphinxReader->getMasterTableOfContents($this->callbackLinks);
		$data = $masterToc ? \Tx_Restdoc_Utility_Helper::getMenuData(\Tx_Restdoc_Utility_Helper::xmlstr_to_array($masterToc)) : array();
		$this->markActiveAndCurrentEntries($data, $this->sphinxReader->getDocument());
		$masterToc = $this->createMasterMenu($data);

		return $masterToc;
	}

	/**
	 * Returns the table of contents.
	 *
	 * @return string
	 */
	public function getTableOfContents() {
		$toc = $this->sphinxReader->getTableOfContents($this->callbackLinks);
		return $toc;
	}

	/**
	 * Returns the body.
	 *
	 * @return string
	 */
	public function getBody() {
		$body = $this->sphinxReader->getBody($this->callbackLinks, $this->callbackImages);
		return $body;
	}

	/**
	 * Returns the title and url of the previous document.
	 *
	 * @return array|NULL
	 */
	public function getPreviousDocument() {
		$previousDocument = $this->sphinxReader->getPreviousDocument();
		if ($previousDocument !== NULL) {
			$absolute = \Tx_Restdoc_Utility_Helper::relativeToAbsolute($this->sphinxReader->getPath() . $this->sphinxReader->getDocument(), '../' . $previousDocument['link']);
			$link = call_user_func($this->callbackLinks, substr($absolute, strlen($this->sphinxReader->getPath())));

			return array(
				'title' => $previousDocument['title'],
				'url' => $link,
			);
		}
		return NULL;
	}

	/**
	 * Returns the title and url of the next document.
	 *
	 * @return array|NULL
	 */
	public function getNextDocument() {
		$nextDocument = $this->sphinxReader->getNextDocument();
		if ($nextDocument !== NULL) {
			if ($this->sphinxReader->getDocument() === $this->sphinxReader->getDefaultFile() . '/' && substr($nextDocument['link'], 0, 3) !== '../') {
				$nextDocumentPath = $this->sphinxReader->getPath();
			} else {
				$nextDocumentPath = $this->sphinxReader->getPath() . $this->sphinxReader->getDocument();
			}
			$absolute = \Tx_Restdoc_Utility_Helper::relativeToAbsolute($nextDocumentPath, '../' . $nextDocument['link']);
			$link = call_user_func($this->callbackLinks, substr($absolute, strlen($this->sphinxReader->getPath())));

			return array(
				'title' => $nextDocument['title'],
				'url' => $link,
			);
		}
		return NULL;
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
	 * Magic getter method for the Sphinx reader.
	 *
	 * @param string $methodName
	 * @param array $arguments
	 * @return mixed|NULL
	 */
	public function __call($methodName, array $arguments) {
		if (is_callable(array($this->sphinxReader, $methodName))) {
			return call_user_func(array($this->sphinxReader, $methodName), $arguments);
		}
		return NULL;
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

}

?>