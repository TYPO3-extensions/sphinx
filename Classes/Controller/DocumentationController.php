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
 * Module 'Sphinx Documentation' for the 'sphinx' extension.
 *
 * @category    Backend Module
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class DocumentationController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * Main action.
	 *
	 * @return void
	 */
	protected function indexAction() {
		// Nothing to do
	}

	/**
	 * Blank action.
	 *
	 * @return void
	 */
	protected function blankAction() {
		// Nothing to do
	}

	/**
	 * Menu action.
	 *
	 * @return void
	 */
	protected function menuAction() {
		$extensions = $this->getExtensionsWithSphinxDocumentation();
		$options = array();
		foreach ($extensions as $extensionKey => $name) {
			$options[$extensionKey] = sprintf('%s (%s)', $name, $extensionKey);
		}
		$this->view->assign('extensions', $options);

		$this->view->assign('showLayouts', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('restdoc'));
		$layouts = array(
			'html' => $this->translate('documentationLayout_typo3'),
			'json' => $this->translate('documentationLayout_interactive'),
		);
		$this->view->assign('layouts', $layouts);
	}

	/**
	 * Render action.
	 *
	 * @param string $extension
	 * @param string $layout
	 * @param boolean $force
	 * @return string
	 */
	protected function renderAction($extension, $layout = 'html', $force = FALSE) {
		if ($extension === '') {
			$this->redirect('blank');
		}
		$documentationUrl = $this->generateDocumentation($extension, $layout, $force);
		$this->view->assign('documentationUrl', $documentationUrl);
	}

	/**
	 * Returns the localized label of a given key.
	 *
	 * @param string $key
	 * @return string
	 */
	protected function translate($key) {
		return \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($key, $this->request->getControllerExtensionKey());
	}

	/**
	 * Generates the documentation for a given extension.
	 *
	 * @param string $extensionKey
	 * @param string $format
	 * @param boolean $force
	 * @return string
	 */
	protected function generateDocumentation($extensionKey, $format = 'html', $force = FALSE) {
		switch ($format) {
			case 'json':
				$documentationType = 'json';
				$masterDocument = 'Index.fjson';
				break;
			case 'html':
			default:
				$documentationType = 'html';
				$masterDocument = 'Index.html';
				break;
		}

		$outputDirectory = PATH_site . 'typo3conf/Documentation/' . $extensionKey . '/' . $documentationType;
		if (!$force && is_file($outputDirectory . '/' . $masterDocument)) {
			// Do not render the documentation again
			$documentationUrl = '../' . substr($outputDirectory, strlen(PATH_site)) . '/' . $masterDocument;
			return $documentationUrl;
		}

		$metadata = $this->getExtensionMetaData($extensionKey);
		$basePath = PATH_site . 'typo3temp/tx_' . $this->request->getControllerExtensionKey() . '/' . $extensionKey;
		\TYPO3\CMS\Core\Utility\GeneralUtility::rmdir($basePath, TRUE);
		\Causal\Sphinx\Utility\SphinxQuickstart::createProject(
			$basePath,
			$extensionKey,
			$metadata['author'],
			FALSE,
			'TYPO3DocEmptyProject',
			$metadata['version'],
			$metadata['release']
		);

		// Recursively instantiate template files
		$source = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extensionKey) . 'Documentation';
		if (!is_dir($source)) {
			$filename = 'typo3temp/tx_' . $this->request->getControllerExtensionKey() . '/1369679343.log';
			$content = 'ERROR 1369679343: Documentation directory was not found: ' . $source;
			\TYPO3\CMS\Core\Utility\GeneralUtility::writeFile(PATH_site . $filename, $content);
			return '../' . $filename;
		}
		$this->recursiveCopy($source, $basePath);

		try {
			if ($format === 'json') {
				\Causal\Sphinx\Utility\SphinxBuilder::buildJson($basePath, '.', '_make/build', '_make/conf.py');
			} else {
				\Causal\Sphinx\Utility\SphinxBuilder::buildHtml($basePath, '.', '_make/build', '_make/conf.py');
			}
		} catch (\RuntimeException $e) {
			$filename = 'typo3temp/tx_' . $this->request->getControllerExtensionKey() . '/' . $e->getCode() . '.log';
			$content = $e->getMessage();
			\TYPO3\CMS\Core\Utility\GeneralUtility::writeFile(PATH_site . $filename, $content);
			return '../' . $filename;
		}

		\TYPO3\CMS\Core\Utility\GeneralUtility::rmdir($outputDirectory, TRUE);
		\TYPO3\CMS\Core\Utility\GeneralUtility::mkdir_deep($outputDirectory . '/');
		$this->recursiveCopy($basePath . '/_make/build/' . $documentationType, $outputDirectory);

		$documentationUrl = '../' . substr($outputDirectory, strlen(PATH_site)) . '/' . $masterDocument;
		return $documentationUrl;
	}

	/**
	 * Recursively copy content from one directory to another.
	 *
	 * @param string $source
	 * @param string $target
	 * @return void
	 */
	protected function recursiveCopy($source, $target) {
		$target = rtrim($target, '/');
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($source,
				\RecursiveDirectoryIterator::SKIP_DOTS),
			\RecursiveIteratorIterator::SELF_FIRST
		);
		foreach ($iterator as $item) {
			if ($item->isDir()) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::mkdir($target . '/' . $iterator->getSubPathName());
			} else {
				copy($item, $target . '/' . $iterator->getSubPathName());
			}
		}
	}

	/**
	 * Returns the list of loaded extensions with Sphinx documentation.
	 *
	 * @return array
	 */
	protected function getExtensionsWithSphinxDocumentation() {
		$extensions = array();
		$loadedExtensions = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getLoadedExtensionListArray();
		foreach ($loadedExtensions as $loadedExtension) {
			$extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($loadedExtension);
			if (is_dir($extPath . 'Documentation') && is_file($extPath . 'Documentation/Index.rst')) {
				$metadata = $this->getExtensionMetaData($loadedExtension);
				$extensions[$loadedExtension] = $metadata['title'];
			}
		}
		asort($extensions);

		return $extensions;
	}

	/**
	 * Returns meta-data for a given extension.
	 *
	 * @param string $extensionKey
	 * @return array
	 */
	protected function getExtensionMetaData($extensionKey) {
		$_EXTKEY = $extensionKey;
		$EM_CONF = array();
		$extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extensionKey);
		include($extPath . 'ext_emconf.php');

		$release = $EM_CONF[$_EXTKEY]['version'];
		list($major, $minor, $_) = explode('.', $release, 3);
		if (($pos = strpos($minor, '-')) !== FALSE) {
			// $minor ~ '2-dev'
			$minor = substr($minor, 0, $pos);
		}
		$EM_CONF[$_EXTKEY]['version'] = $major . '.' . $minor;
		$EM_CONF[$_EXTKEY]['release'] = $release;

		return $EM_CONF[$_EXTKEY];
	}

}

?>