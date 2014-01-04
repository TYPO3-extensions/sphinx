<?php
namespace Causal\Sphinx\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013-2014 Xavier Perseguers <xavier@causal.ch>
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
 * Interactive Documentation Viewer for the 'sphinx' extension.
 *
 * @category    Backend Module
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class InteractiveViewerController extends AbstractActionController {

	/** @var \Tx_Restdoc_Reader_SphinxJson */
	protected $sphinxReader;

	/** @var string */
	protected $reference;

	/** @var string */
	protected $extension;

	/** @var string */
	protected $languageDirectory = 'default';

	// public for use with htmlizeWarnings()
	public $uriBuilder;

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
	 * Render action.
	 *
	 * @param string $reference Reference of a documentation
	 * @param string $document Name of the document/chapter to show
	 * @param string $documentationFilename Absolute path to the corresponding documentation source file
	 * @return void|string
	 * @throws \RuntimeException
	 */
	protected function renderAction($reference, $document = '', $documentationFilename = '') {
		$this->checkExtensionRestdoc();
		$this->reference = $reference;

		if (empty($document)) {
			$document = $this->getBackendUser()->getModuleData('help_documentation/DocumentationController/reference-' . $reference);
		}

		list($type, $identifier) = explode(':', $reference, 2);
		switch ($type) {
			case 'EXT':
				list($extensionKey, $locale) = explode('.', $identifier, 2);
				$this->languageDirectory = empty($locale) ? 'default' : $locale;
				$this->extension = $extensionKey;
				$path = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('typo3conf/Documentation/typo3cms.extensions.' . $extensionKey . '/' . $this->languageDirectory . '/json');
			break;
			case 'USER':
				if (is_file($documentationFilename)) {
					$path = dirname($documentationFilename);
				} else {
					$path = '';
					$this->signalSlotDispatcher->dispatch(
						__CLASS__,
						'retrieveBasePath',
						array(
							'identifier' => $identifier,
							'path' => &$path,
						)
					);
				}
			break;
			default:
				throw new \RuntimeException('Unknown reference "' . $reference . '"', 1371163248);
		}

		if (empty($document)) {
			$document = $this->sphinxReader->getDefaultFile() . '/';
		}

		if (!is_dir($path)) {
			return 'Path ' . $path . ' cannot be resolved. You probably have a write permission issue.';
		}

		$this->sphinxReader
			->setPath($path)
			->setDocument($document)
			->load();

		// Store preferences
		$this->getBackendUser()->pushModuleData('help_documentation/DocumentationController/reference-' . $reference, $document);

		/** @var \Causal\Sphinx\Domain\Model\Documentation $documentation */
		$documentation = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Causal\Sphinx\Domain\Model\Documentation', $this->sphinxReader);
		$documentation->setCallbackLinks(array($this, 'getLink'));
		$documentation->setCallbackImages(array($this, 'processImage'));

		$this->view->assign('documentation', $documentation);
		$this->view->assign('reference', $reference);
		$this->view->assign('document', $document);

		$warningsFilename = '';
		if (file_exists($path . '/warnings.txt')) {
			$warningsFilename = $this->htmlizeWarnings($path, $reference);
		}

		$buttons = $this->getButtons($reference, $document, $warningsFilename);
		$this->view->assign('buttons', $buttons);

		$this->view->assign('editUrl', $this->getEditUrl($reference, $document, TRUE));
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
		list($minVersion, $_) = explode('-', $metadata['constraints']['suggests']['restdoc']);

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
				'reference' => $this->reference,
				'document' => $document
			)
		);
		switch (TRUE) {
			case $anchor !== '':
				$link .= '#' . $anchor;
			break;
			case substr($document, 0, 11) === '_downloads/':
			case substr($document, 0, 8) === '_images/':
			case substr($document, 0, 9) === '_sources/':
				$link = '../typo3conf/Documentation/typo3cms.extensions.' . $this->extension . '/' . $this->languageDirectory . '/json/' . $document;
			break;
		}
		return $link;
	}

	/**
	 * Processes an image.
	 *
	 * @param array $data Image information
	 * @return string HTML image tag
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

	/**
	 * Returns the toolbar buttons.
	 *
	 * @param string $reference
	 * @param string $document
	 * @param string $warningsFilename
	 * @return string
	 */
	protected function getButtons($reference, $document, $warningsFilename) {
		$buttons = array();

		if ($document !== 'genindex/') {
			$buttons[] = $this->createToolbarButton(
				$this->getEditUrl($reference, $document),
				$this->translate('toolbar.interactive.edit'),
				't3-icon-actions t3-icon-actions-page t3-icon-page-open'
			);
		}
		if (!empty($warningsFilename)) {
			$buttons[] = $this->createToolbarButton(
				$warningsFilename,
				$this->translate('toolbar.interactive.showWarnings'),
				't3-icon-status t3-icon-status-dialog t3-icon-dialog-warning'
			);
		}

		$translations = $this->getTranslations($reference, $document);
		if (count($translations) > 0) {
			$buttons[] = '<div style="float:right">';
			$numberOfTranslations = count($translations);
			for ($i = 0; $i < $numberOfTranslations; $i++) {
				if ($i > 0) {
					$buttons[] = '|';
				}
				if ($translations[$i]['active']) {
					$buttons[] = sprintf(
						'<strong>%s</strong>',
						htmlspecialchars($translations[$i]['name'])
					);
				} else {
					$buttons[] = sprintf(
						'<a href="%s">%s</a>',
						$translations[$i]['link'],
						htmlspecialchars($translations[$i]['name'])
					);
				}
			}
			$buttons[] = '</div>';
		}

		return implode(' ', $buttons);
	}

	/**
	 * Returns the edit URL for a given reference/document.
	 *
	 * @param string $reference
	 * @param string $document
	 * @param boolean $createAbsoluteUri
	 * @return string
	 */
	protected function getEditUrl($reference, $document, $createAbsoluteUri = FALSE) {
		if ($createAbsoluteUri) {
			$this->uriBuilder->setCreateAbsoluteUri(TRUE);
		}
		$url = $this->uriBuilder->uriFor(
			'edit',
			array(
				'reference' => $reference,
				'document' => $document,
			),
			'RestEditor'
		);
		return $url;
	}

	/**
	 * Returns links to translate current document.
	 * Note: This is currently only implemented for extension manuals.
	 *
	 * @param string $reference
	 * @param string $document
	 * @return array
	 */
	protected function getTranslations($reference, $document) {
		$translations = array();

		list($type, $identifier) = explode(':', $reference, 2);
		if ($type === 'EXT') {
			list($extensionKey, $locale) = explode('.', $identifier, 2);

			if (empty($locale)) {
				$documentationType = \Causal\Sphinx\Utility\GeneralUtility::getDocumentationType($extensionKey);
			} else {
				$documentationType = \Causal\Sphinx\Utility\GeneralUtility::getLocalizedDocumentationType($extensionKey, $locale);
			}

			if ($documentationType === \Causal\Sphinx\Utility\GeneralUtility::DOCUMENTATION_TYPE_SPHINX) {
				$localizationDirectories = \Causal\Sphinx\Utility\GeneralUtility::getLocalizationDirectories($extensionKey);
				$extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extensionKey);

				$filename = ($document ? substr($document, 0, -1) : 'Index') . '.rst';
				$absoluteFilename = $extensionPath . 'Documentation/' . $filename;
				if (is_file($absoluteFilename) && count($localizationDirectories) > 0) {
					// Current document exists in English, will try to find a match in translated versions
					foreach ($localizationDirectories as $localizationDirectory) {
						$absoluteFilename = $extensionPath . $localizationDirectory['directory'] . '/' . $filename;
						$localizationLocale = $localizationDirectory['locale'];

						if (is_file($absoluteFilename) && !isset($translations[$localizationLocale])) {
							$translations[$localizationLocale] = array(
								'name' => $localizationLocale,
								'link' => $this->uriBuilder->uriFor(
									'index',
									array(
										'reference' => 'EXT:' . $extensionKey . '.' . $localizationLocale,
										'document' => $document,
										'layout' => 'json',
									),
									'Documentation'
								),
								'active' => ($locale === $localizationLocale),
							);
						}
					}
					if (count($translations) > 0) {
						// Prepend English version
						array_unshift($translations, array(
							'name' => 'en_US',
							'link' => $this->uriBuilder->uriFor(
								'index',
								array(
									'reference' => 'EXT:' . $extensionKey,
									'document' => $document,
									'layout' => 'json',
								),
								'Documentation'
							),
							'active' => empty($locale),
						));
					}
				}
			}
		}

		return array_values($translations);
	}

	/**
	 * HTML-ize a warnings.txt file.
	 *
	 * @param string $path Directory containing warnings.txt
	 * @param string $reference
	 * @return string
	 */
	protected function htmlizeWarnings($path, $reference) {
		$path = rtrim($path, '/') . '/';
		$mtime = filemtime($path . 'warnings.txt');

		$cacheDirectory = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('typo3temp/tx_sphinx/');
		$cacheFiles = glob($cacheDirectory . 'warnings-' . md5($path) . '.*');
		if (count($cacheFiles) > 0) {
			list($_, $timestamp) = explode('.', basename($cacheFiles[0]));
			if ($timestamp == $mtime) {
				return \TYPO3\CMS\Core\Utility\PathUtility::getRelativePathTo(dirname($cacheFiles[0]),
					PATH_site) . basename($cacheFiles[0]);
			} else {
				// Cache file is now outdated
				@unlink($cacheFiles[0]);
			}
		}

		// Cache does not exist or was outdated
		$contents = file_get_contents($path . 'warnings.txt');

		// Convert new lines for HTML output
		$contents = nl2br($contents);

		/** @var \Causal\Sphinx\Controller\RestEditorController $restEditorController */
		$restEditorController = $this->objectManager->get('Causal\\Sphinx\\Controller\\RestEditorController');
		$parts = $restEditorController->parseReferenceDocument($reference, 'Index/');

		$basePath = $parts['basePath'] . '/';
		if ($parts['type'] === 'EXT') {
			// $basePath is potentially the physical path (in case of symbolic link)
			// but we need a path within PATH_site to be detected and replaced
			$basePath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($parts['extensionKey']) . 'Documentation/';

			if (!empty($parts['locale'])) {
				$basePath .= 'Localization.' . $parts['locale'] . '/';
			}
		}

		// Compatibility with Windows platform
		$basePath = str_replace('/', DIRECTORY_SEPARATOR, $basePath);

		$self = $this;
		$contents = preg_replace_callback(
			'#' . preg_quote($basePath, '#') . '([^: ]+)(<br />|:(\d*):)#m',
			function($matches) use ($reference, $self) {
				$filename = $matches[1];
				if (count($matches) == 4) {
					$line = max(1, intval($matches[3]));
					$linkPattern = '<a href="%s">%s</a>:%s:';
				} else {
					$line = 1;
					$linkPattern = '<a href="%s">%s</a><br />';
				}

				if (substr($filename, -4) === '.rst') {
					$document = substr($filename, 0, -4) . '/';
					$actionUrl = $self->uriBuilder->uriFor(
						'edit',
						array(
							'reference' => $reference,
							'document' => $document,
							'startLine' => $line,
						),
						'RestEditor'
					);

					$baseUrl = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . 'typo3/';
					return sprintf(
							$linkPattern,
							$baseUrl . $actionUrl,
							htmlspecialchars($filename),
							$line
					);
				} else {
					// No change to contents
					return $matches[0];
				}
			},
			$contents
		);

		$htmlTemplate = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>$reference: warnings.txt</title>
	<style type="text/css">
		body {
			font-family: Verdana,Arial,Helvetica,sans-serif;
			font-size: 11px;
			line-height: 14px;
		}
	</style>
</head>
<body>
###CONTENTS###
</body>
</html>
HTML;

		$contents = str_replace('###CONTENTS###', $contents, $htmlTemplate);
		$cacheFile = $cacheDirectory . 'warnings-' . md5($path) . '.' . $mtime . '.html';
		\TYPO3\CMS\Core\Utility\GeneralUtility::writeFile($cacheFile, $contents);

		return \TYPO3\CMS\Core\Utility\PathUtility::getRelativePathTo(dirname($cacheFile), PATH_site) . basename($cacheFile);
	}

}
