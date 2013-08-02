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
	 * @var \Causal\Sphinx\Domain\Repository\ExtensionRepository
	 * @inject
	 */
	protected $extensionRepository;

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

		$this->view->assign('references', $references);

		$layouts = array(
			'html' => $this->translate('documentationLayout_static'),
			'json' => $this->translate('documentationLayout_interactive'),
		);

		switch ($this->settings['pdf_builder']) {
			case 'pdflatex':
				$renderPdf = \TYPO3\CMS\Core\Utility\CommandUtility::getCommand('pdflatex') !== '';
				break;
			case 'rst2pdf':
			default:
				$renderPdf = TRUE;
				break;
		}
		if ($renderPdf) {
			$layouts['pdf'] = $this->translate('documentationLayout_pdf');
		}
		$this->view->assign('layouts', $layouts);

		$currentReference = $this->getBackendUser()->getModuleData('help_documentation/DocumentationController/reference');
		$currentLayout = $this->getBackendUser()->getModuleData('help_documentation/DocumentationController/layout');
		$this->view->assign('currentReference', $currentReference);
		$this->view->assign('currentLayout', $currentLayout);
	}

	/**
	 * Render action.
	 *
	 * @param string $reference
	 * @param string $layout
	 * @param boolean $force
	 * @return string
	 * @throws \RuntimeException
	 */
	protected function renderAction($reference = '', $layout = 'html', $force = FALSE) {
		// Store preferences
		$this->getBackendUser()->pushModuleData('help_documentation/DocumentationController/reference', $reference);
		$this->getBackendUser()->pushModuleData('help_documentation/DocumentationController/layout', $layout);

		if ($reference === '') {
			$this->redirect('blank');
		}

		list($type, $identifier) = explode(':', $reference, 2);
		switch ($type) {
			case 'EXT':
				$extensionKey = $identifier;
				$documentationUrl = \Causal\Sphinx\Utility\GeneralUtility::generateDocumentation($extensionKey, $layout, $force);
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

		if ($layout === 'json' && substr($documentationUrl, -6) === '.fjson') {
			if (substr($documentationUrl, 0, 3) === '../') {
				$documentationFilename = PATH_site . substr($documentationUrl, 3);
			} elseif ($documentationUrl{0} === '/') {
				$documentationFilename = PATH_site . substr($documentationUrl, 1);
			} else {
				$documentationFilename = '';
			}
			$this->forward('render', 'InteractiveViewer', NULL, array('reference' => $reference, 'documentationFilename' => $documentationFilename));
		}
		$this->view->assign('documentationUrl', $documentationUrl);
	}

}

?>