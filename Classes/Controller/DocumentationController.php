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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Causal\Sphinx\Utility\MiscUtility;

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
class DocumentationController extends AbstractActionController {

	/**
	 * Extension repository
	 *
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
	 * Main action.
	 *
	 * @param string $reference Reference of a documentation
	 * @param string $document The document (used only with $layout = 'json')
	 * @param string $layout Layout to use
	 * @param boolean $force TRUE if rendering should be forced, otherwise FALSE to use cache if available
	 * @return void
	 */
	protected function indexAction($reference = NULL, $document = '', $layout = '', $force = FALSE) {
		$references = $this->getReferences();
		$layouts = $this->getLayouts();

		if ($reference === NULL) {
			$currentReference = $this->getBackendUser()->getModuleData('help_documentation/DocumentationController/reference');
		} else {
			// Store preferences
			$this->getBackendUser()->pushModuleData('help_documentation/DocumentationController/reference', $reference);
			$currentReference = $reference;
		}
		if (empty($layout)) {
			$currentLayout = $this->getBackendUser()->getModuleData('help_documentation/DocumentationController/layout');
		} else {
			// Store preferences
			$this->getBackendUser()->pushModuleData('help_documentation/DocumentationController/layout', $layout);
			$currentLayout = $layout;
		}

		if (empty($currentReference)) {
			$contentActionUrl = $this->uriBuilder->uriFor('dashboard');
		} else {
			$contentActionUrl = $this->uriBuilder->uriFor(
				'render',
				array(
					'reference' => $currentReference,
					'document' => $document,
					'layout' => $currentLayout,
					'force' => $force,
				)
			);
		}

		$this->view->assignMultiple(array(
			'references' => $references,
			'layouts' => $layouts,
			'force' => $force,
			'currentReference' => $currentReference,
			'currentLayout' => $currentLayout,
			'contentActionUrl' => $contentActionUrl,
		));
	}

	/**
	 * Dashboard action.
	 *
	 * @return void
	 */
	protected function dashboardAction() {
		$extensionsWithoutDocumentation = $this->extensionRepository->findByHasNoDocumentation('G,L');
		$extensionWithOpenOfficeDocumentation = $this->extensionRepository->findByHasOpenOffice('G,L');
		$customProjects = $this->projectRepository->findAll();

		$this->view->assignMultiple(array(
			'extensionsEmpty' => $extensionsWithoutDocumentation,
			'extensionsOpenOffice' => $extensionWithOpenOfficeDocumentation,
			'customProjects' => $customProjects,
			'oldTYPO3' => version_compare(TYPO3_version, '6.1.99', '<='),
			'layout' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('restdoc') ? 'json' : 'html',
		));
	}

	/**
	 * Render action.
	 *
	 * @param string $reference Reference of a documentation
	 * @param string $document The document (used only with $layout = 'json')
	 * @param string $layout Layout to use
	 * @param boolean $force TRUE if rendering should be forced, otherwise FALSE to use cache if available
	 * @return void
	 * @throws \RuntimeException
	 */
	protected function renderAction($reference = '', $document = '', $layout = 'html', $force = FALSE) {
		list($type, $identifier) = explode(':', $reference, 2);
		switch ($type) {
			case 'EXT':
				list($extensionKey, $locale) = explode('.', $identifier, 2);
				$documentationUrl = MiscUtility::generateDocumentation($extensionKey, $layout, $force, $locale);
			break;
			case 'USER':
				$documentationUrl = NULL;
				$this->signalSlotDispatcher->dispatch(
					__CLASS__,
					'renderUserDocumentation',
					array(
						'identifier' => $identifier,
						'layout' => $layout,
						'force' => $force,
						'documentationUrl' => &$documentationUrl,
					)
				);
				if ($documentationUrl === NULL) {
					throw new \RuntimeException('No slot found to render documentation with identifier "' . $identifier . '"', 1371208253);
				}
			break;
			default:
				throw new \RuntimeException('Unknown reference "' . $reference . '"', 1371162948);
		}

		if (GeneralUtility::inList('.pdf,.html,.log', substr($documentationUrl, strrpos($documentationUrl, '.')))) {
			// Prevent browser-cache issue
			$documentationUrl .= '?t=' . $GLOBALS['EXEC_TIME'];
		}

		if ($layout === 'json' && substr($documentationUrl, -6) === '.fjson') {
			if (substr($documentationUrl, 0, 3) === '../') {
				$documentationFilename = GeneralUtility::getFileAbsFileName(substr($documentationUrl, 3));
			} elseif ($documentationUrl{0} === '/') {
				$documentationFilename = GeneralUtility::getFileAbsFileName(substr($documentationUrl, 1));
			} else {
				$documentationFilename = '';
			}

			if (empty($document)) {
				$document = $this->getBackendUser()->getModuleData('help_documentation/DocumentationController/reference-' . $reference);
			}

			$this->forward(
				'render',
				'InteractiveViewer',
				NULL,
				array(
					'reference' => $reference,
					'document' => $document,
					'documentationFilename' => $documentationFilename
				)
			);
		}

		if (substr(preg_replace('/\?t=\d+$/', '', $documentationUrl), -4) === '.pdf') {
			$referer = GeneralUtility::getIndpEnv('HTTP_REFERER');
			if (substr($referer, strpos($referer, '?M=') + 3) === 'help_SphinxDocumentation') {
				$this->view->assign('documentationUrl', $documentationUrl);
				return;
			}
		}
		$this->redirectToUri($documentationUrl);
	}

	/**
	 * Converts an OpenOffice manual into a Sphinx project.
	 *
	 * @param string $extensionKey The TYPO3 extension key
	 * @return void
	 */
	protected function convertAction($extensionKey) {
		$extensionPath = MiscUtility::extPath($extensionKey);
		$sxwFilename = $extensionPath . 'doc/manual.sxw';
		$documentationDirectory = $extensionPath . 'Documentation';
		$reference = NULL;

		if (is_file($sxwFilename)) {
			try {
				\Causal\Sphinx\Utility\OpenOfficeConverter::convert($sxwFilename, $documentationDirectory);
				$reference = 'EXT:' . $extensionKey;
			} catch (\RuntimeException $exception) {
				$this->controllerContext->getFlashMessageQueue()->enqueue(
					GeneralUtility::makeInstance(
						'TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
						$exception->getMessage(),
						'',
						\TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR,
						TRUE
					)
				);
			}
		}

		// Open converted documentation
		$this->redirect('index', NULL, NULL, array('reference' => $reference));
	}

	/**
	 * Creates a Sphinx documentation project for a given extension.
	 *
	 * @param string $extensionKey The TYPO3 extension key
	 * @return void
	 */
	protected function createExtensionProjectAction($extensionKey) {
		$extensionPath = MiscUtility::extPath($extensionKey);
		$documentationDirectory = $extensionPath . 'Documentation';
		$reference = NULL;

		try {
			GeneralUtility::mkdir_deep($documentationDirectory . DIRECTORY_SEPARATOR);

			$metadata = MiscUtility::getExtensionMetaData($extensionKey);
			\Causal\Sphinx\Utility\SphinxQuickstart::createProject(
				$documentationDirectory,
				$metadata['title'],
				$metadata['author'],
				FALSE,
				'TYPO3DocProject',
				$metadata['version'],
				$metadata['release'],
				$extensionKey
			);
			$reference = 'EXT:' . $extensionKey;
		} catch (\RuntimeException $exception) {
			$this->controllerContext->getFlashMessageQueue()->enqueue(
				GeneralUtility::makeInstance(
					'TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
					$exception->getMessage(),
					'',
					\TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR,
					TRUE
				)
			);
		}

		// Open freshly created documentation
		$this->redirect('index', NULL, NULL, array('reference' => $reference));
	}

	/**
	 * Returns the available references.
	 *
	 * @return array
	 */
	protected function getReferences() {
		$extensions = $this->extensionRepository->findByHasSphinxDocumentation();
		$references = array();
		foreach ($extensions as $extension) {
			$typeLabel = $this->translate('extensionType_' . $extension->getInstallType());
			$references[$typeLabel]['EXT:' . $extension->getExtensionKey()] = sprintf('%s (%s)', $extension->getTitle(), $extension->getExtensionKey());
		}

		$this->signalSlotDispatcher->dispatch(
			__CLASS__,
			'afterInitializeReferences',
			array(
				'references' => &$references,
			)
		);

		return $references;
	}

	/**
	 * Returns the available layouts.
	 *
	 * @return array
	 */
	public function getLayouts() {
		$layouts = array(
			'html' => $this->translate('documentationLayout_static'),
			'json' => $this->translate('documentationLayout_interactive'),
		);

		$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sphinx']);
		switch ($configuration['pdf_builder']) {
			case 'pdflatex':
				$renderPdf = \TYPO3\CMS\Core\Utility\CommandUtility::getCommand('pdflatex') !== '';
			break;
			case 'rst2pdf':
				$renderPdf = TRUE;
			break;
			default:
				$renderPdf = FALSE;
			break;
		}
		if ($renderPdf) {
			$layouts['pdf'] = $this->translate('documentationLayout_pdf');
		}

		return $layouts;
	}

}
