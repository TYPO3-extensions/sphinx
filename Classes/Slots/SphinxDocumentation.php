<?php
namespace Causal\Sphinx\Slots;

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
 * Slot implementation for EXT:documentation.
 *
 * @category    Slots
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class SphinxDocumentation {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;

	/**
	 * Post-processes the list of available documents.
	 *
	 * @param string $language
	 * @param array $documents
	 * @return void
	 */
	public function postProcessDocuments($language, array &$documents) {
		$extensionsWithSphinxDocumentation = $this->getExtensionsWithSphinxDocumentation();
		foreach ($extensionsWithSphinxDocumentation as $extensionKey => $info) {
			$packageKey = 'typo3cms.extensions.' . $extensionKey;

			/** @var \TYPO3\CMS\Documentation\Domain\Model\Document $document */
			/** @var \TYPO3\CMS\Documentation\Domain\Model\DocumentTranslation $documentTranslation */

			if (!isset($documents[$packageKey])) {
				$document = $this->objectManager->get('TYPO3\\CMS\\Documentation\\Domain\\Model\\Document')
					->setPackageKey($packageKey)
					->setIcon($info['ext_icon']);
				$documents[$packageKey] = $document;
			}

			$document = $documents[$packageKey];
			$documentTranslation = NULL;
			foreach ($document->getTranslations() as $translation) {
				/** @var \TYPO3\CMS\Documentation\Domain\Model\DocumentTranslation $translation */
				if ($translation->getLanguage() === 'default') {
					$documentTranslation = $translation;
					break;
				}
			}

			if ($documentTranslation === NULL) {
				$documentTranslation = $this->objectManager->get('TYPO3\\CMS\\Documentation\\Domain\\Model\\DocumentTranslation')
					->setLanguage('default')
					->setTitle($info['title'])
					->setDescription($info['description']);

				$document->addTranslation($documentTranslation);
			}

			$existingFormats = array();
			foreach ($documentTranslation->getFormats() as $documentFormat) {
				if ($documentFormat->getFormat() === 'sxw') {
					// Remove OpenOffice from the list when HTML/PDF is available
					$documentTranslation->removeFormat($documentFormat);
					continue;
				}
				$existingFormats[$documentFormat->getFormat()] = $documentFormat;
			}

			$formats = $this->getSupportedFormats();
			foreach ($formats as $format) {
				if (!isset($existingFormats[$format])) {
					/** @var \TYPO3\CMS\Documentation\Domain\Model\DocumentFormat $documentFormat */
					$documentFormat = $this->objectManager->get('TYPO3\\CMS\\Documentation\\Domain\\Model\\DocumentFormat')
						->setFormat($format)
						->setPath($this->getRenderLink($extensionKey, $format));

					$documentTranslation->addFormat($documentFormat);
				} else {
					// Override path of the document to point to EXT:sphinx's renderer
					$existingFormats[$format]->setPath($this->getRenderLink($extensionKey, $format));
				}
			}
		}
	}

	/**
	 * Returns the list of loaded extensions with Sphinx documentation.
	 *
	 * @return array
	 */
	protected function getExtensionsWithSphinxDocumentation() {
		$loadedExtensions = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getLoadedExtensionListArray();
		$extensions = array();
		$titles = array();

		foreach ($loadedExtensions as $loadedExtension) {
			$info = $GLOBALS['TYPO3_LOADED_EXT'][$loadedExtension];

			$documentationType = \Causal\Sphinx\Utility\GeneralUtility::getDocumentationType($loadedExtension);
			if ($documentationType !== \Causal\Sphinx\Utility\GeneralUtility::DOCUMENTATION_TYPE_UNKNOWN) {
				$metadata = \Causal\Sphinx\Utility\GeneralUtility::getExtensionMetaData($loadedExtension);
				$extensions[$loadedExtension] = array(
					'title'       => $metadata['title'],
					'description' => $metadata['description'],
					'ext_icon'    => $info['siteRelPath'] . $info['ext_icon'],
					'type'        => $info['type'],
				);
				$titles[$loadedExtension] = strtolower($metadata['title']);
			}
		}
		array_multisort($titles, SORT_ASC, $extensions);

		return $extensions;
	}

	/**
	 * Returns the supported documentation rendering formats.
	 *
	 * @return array
	 */
	protected function getSupportedFormats() {
		$formats = array('html', 'json');

		$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sphinx']);
		switch ($configuration['pdf_builder']) {
			case 'pdflatex':
				$renderPdf = \TYPO3\CMS\Core\Utility\CommandUtility::getCommand('pdflatex') !== '';
				break;
			case 'rst2pdf':
				$renderPdf = TRUE;
				break;
		}
		if ($renderPdf) {
			$formats[] = 'pdf';
		}

		return $formats;
	}

	/**
	 * @param string $extensionKey
	 * @param string $format
	 * @return string
	 */
	protected function getRenderLink($extensionKey, $format) {
		/** @var \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder $uriBuilder */
		$uriBuilder = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Routing\\UriBuilder');
		$request = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Request');
		$uriBuilder->setRequest($request);

		$link = 'typo3/' . $uriBuilder->uriFor(
			'render',
			array(
				'reference' => 'EXT:' . $extensionKey,
				'layout' => $format,
				'force' => FALSE,
			),
			'Documentation',
			'sphinx',
			'help_sphinxdocumentation'
		);

		// TODO: better way to change module?
		$link = str_replace('?M=help_DocumentationDocumentation', '?M=help_SphinxDocumentation', $link);

		return $link;
	}

}

?>