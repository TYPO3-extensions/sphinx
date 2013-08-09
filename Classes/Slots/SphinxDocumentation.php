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
	 * @var \Causal\Sphinx\Domain\Repository\ExtensionRepository
	 * @inject
	 */
	protected $extensionRepository;

	/**
	 * @var \Causal\Sphinx\Domain\Repository\ProjectRepository
	 * @inject
	 */
	protected $projectRepository;

	/**
	 * Post-processes the list of available documents.
	 *
	 * @param string $language
	 * @param array $documents
	 * @return void
	 */
	public function postProcessDocuments($language, array &$documents) {
		$formats = $this->getSupportedFormats();

		$extensionsWithSphinxDocumentation = $this->extensionRepository->findByHasSphinxDocumentation();
		foreach ($extensionsWithSphinxDocumentation as $extension) {
			/** @var \TYPO3\CMS\Documentation\Domain\Model\Document $document */
			/** @var \TYPO3\CMS\Documentation\Domain\Model\DocumentTranslation $documentTranslation */

			$packageKey = $extension->getPackageKey();

			if (!isset($documents[$packageKey])) {
				$document = $this->objectManager->get('TYPO3\\CMS\\Documentation\\Domain\\Model\\Document')
					->setPackageKey($packageKey)
					->setIcon($extension->getIcon());
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
					->setTitle($extension->getTitle())
					->setDescription($extension->getDescription());

				$document->addTranslation($documentTranslation);
			}

			$existingFormats = array();
			foreach ($documentTranslation->getFormats() as $documentFormat) {
				/** @var $documentFormat \TYPO3\CMS\Documentation\Domain\Model\DocumentFormat */
				if ($documentFormat->getFormat() === 'sxw') {
					// Remove OpenOffice from the list when HTML/PDF is available
					$documentTranslation->removeFormat($documentFormat);
					continue;
				}
				$existingFormats[$documentFormat->getFormat()] = $documentFormat;
			}

			foreach ($formats as $format) {
				if (!isset($existingFormats[$format])) {
					/** @var \TYPO3\CMS\Documentation\Domain\Model\DocumentFormat $documentFormat */
					$documentFormat = $this->objectManager->get('TYPO3\\CMS\\Documentation\\Domain\\Model\\DocumentFormat')
						->setFormat($format)
						->setPath($this->getRenderLink($extension->getExtensionKey(), $format));

					$documentTranslation->addFormat($documentFormat);
				} else {
					// Override path of the document to point to EXT:sphinx's renderer
					$existingFormats[$format]->setPath($this->getRenderLink($extension->getExtensionKey(), $format));
				}
			}
		}

		$defaultIcon = '../' . substr(
			\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('documentation') . 'ext_icon.gif',
			strlen(PATH_site)
		);
		$projects = $this->projectRepository->findAll();
		foreach ($projects as $project) {
			$packageKey = $project->getDocumentationKey();

			/** @var \TYPO3\CMS\Documentation\Domain\Model\Document $document */
			$document = $this->objectManager->get('TYPO3\\CMS\\Documentation\\Domain\\Model\\Document')
				->setPackageKey($packageKey)
				->setIcon($defaultIcon);

			/** @var \TYPO3\CMS\Documentation\Domain\Model\DocumentTranslation $documentTranslation */
			$documentTranslation = $this->objectManager->get('TYPO3\\CMS\\Documentation\\Domain\\Model\\DocumentTranslation')
				->setLanguage('default')
				->setTitle($project->getName())
				->setDescription($project->getDescription());

			foreach ($formats as $format) {
				/** @var \TYPO3\CMS\Documentation\Domain\Model\DocumentFormat $documentFormat */
				$documentFormat = $this->objectManager->get('TYPO3\\CMS\\Documentation\\Domain\\Model\\DocumentFormat')
					->setFormat($format)
					->setPath($this->getRenderLink($project->getDocumentationKey(), $format, 'USER'));

				$documentTranslation->addFormat($documentFormat);
			}

			$document->addTranslation($documentTranslation);
			$documents[$packageKey] = $document;
		}
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
			default:
				$renderPdf = TRUE;
				break;
		}
		if ($renderPdf) {
			$formats[] = 'pdf';
		}

		return $formats;
	}

	/**
	 * Returns a rendering link.
	 *
	 * @param string $reference
	 * @param string $format
	 * @param string $referenceType
	 * @return string
	 */
	protected function getRenderLink($reference, $format, $referenceType = 'EXT') {
		/** @var \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder $uriBuilder */
		$uriBuilder = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Routing\\UriBuilder');
		$request = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Request');
		$uriBuilder->setRequest($request);

		$link = 'typo3/' . $uriBuilder->uriFor(
			'render',
			array(
				'reference' => $referenceType . ':' . $reference,
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