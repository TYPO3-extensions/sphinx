<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with TYPO3 source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Causal\Sphinx\Controller;

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
class DocumentationController extends AbstractActionController
{

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
     * @param boolean $force true if rendering should be forced, otherwise false to use cache if available
     * @return void
     */
    public function indexAction($reference = null, $document = '', $layout = '', $force = false)
    {
        $references = $this->getReferences();
        $layouts = $this->getLayouts();

        if ($reference === null) {
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
            'typo3_8x' => version_compare(TYPO3_branch, '8', '>='),
        ));
    }

    /**
     * Dashboard action.
     *
     * @return void
     */
    public function dashboardAction()
    {
        $extensionsWithoutDocumentation = $this->extensionRepository->findByHasNoDocumentation('G,L');
        $extensionWithOpenOfficeDocumentation = $this->extensionRepository->findByHasOpenOffice('G,L');
        $customProjects = $this->projectRepository->findAll();

        $this->view->assignMultiple(array(
            'extensionsEmpty' => $extensionsWithoutDocumentation,
            'extensionsOpenOffice' => $extensionWithOpenOfficeDocumentation,
            'customProjects' => $customProjects,
            'layout' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('restdoc') ? 'json' : 'html',
            'typo3_8x' => version_compare(TYPO3_branch, '8', '>='),
        ));
    }

    /**
     * Render action.
     *
     * @param string $reference Reference of a documentation
     * @param string $document The document (used only with $layout = 'json')
     * @param string $layout Layout to use
     * @param boolean $force true if rendering should be forced, otherwise false to use cache if available
     * @return void
     * @throws \RuntimeException
     */
    public function renderAction($reference = '', $document = '', $layout = 'html', $force = false)
    {
        list($type, $identifier) = explode(':', $reference, 2);
        switch ($type) {
            case 'EXT':
                list($extensionKey, $locale) = explode('.', $identifier, 2);
                $documentationUrl = MiscUtility::generateDocumentation($extensionKey, $layout, $force, $locale);
                break;
            case 'USER':
                $documentationUrl = null;
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
                if ($documentationUrl === null) {
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
                null,
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
    public function convertAction($extensionKey)
    {
        $extensionPath = MiscUtility::extPath($extensionKey);
        $sxwFilename = $extensionPath . 'doc/manual.sxw';
        $documentationDirectory = $extensionPath . 'Documentation/';
        $reference = null;

        if (is_file($sxwFilename)) {
            try {
                /** @var \Causal\Sphinx\Utility\OpenOfficeConverter $openOfficeConverter */
                $openOfficeConverter = GeneralUtility::makeInstance(\Causal\Sphinx\Utility\OpenOfficeConverter::class);
                $openOfficeConverter->convert($sxwFilename, $documentationDirectory, $extensionKey);
                $reference = 'EXT:' . $extensionKey;

                // Prevent any cache issue when rsyinc'ing files to be rendered (this happens
                // when converting the same manual again and again). The path is the same as in
                // \Causal\Sphinx\Utility\MiscUtility::generateDocumentation()
                $temporaryPath = PATH_site . 'typo3temp/tx_sphinx/' . $extensionKey;
                $renderPath = PATH_site . 'typo3conf/Documentation/typo3cms.extensions.' . $extensionKey;
                GeneralUtility::rmdir($temporaryPath, true);
                GeneralUtility::rmdir($renderPath, true);
            } catch (\RuntimeException $exception) {
                $this->controllerContext->getFlashMessageQueue()->enqueue(
                    GeneralUtility::makeInstance(
                        \TYPO3\CMS\Core\Messaging\FlashMessage::class,
                        $exception->getMessage(),
                        '',
                        \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR,
                        true
                    )
                );
            }
        }

        // Open converted documentation
        $this->redirect('index', null, null, array('reference' => $reference));
    }

    /**
     * Creates a Sphinx documentation project for a given extension.
     *
     * @param string $extensionKey The TYPO3 extension key
     * @return void
     */
    public function createExtensionProjectAction($extensionKey)
    {
        $extensionPath = MiscUtility::extPath($extensionKey);
        $documentationDirectory = $extensionPath . 'Documentation';
        $reference = null;

        try {
            GeneralUtility::mkdir_deep($documentationDirectory . DIRECTORY_SEPARATOR);

            $metadata = MiscUtility::getExtensionMetaData($extensionKey);
            \Causal\Sphinx\Utility\SphinxQuickstart::createProject(
                $documentationDirectory,
                $metadata['title'],
                $metadata['author'],
                false,
                'TYPO3DocProject',
                $metadata['version'],
                $metadata['release'],
                $extensionKey
            );
            $reference = 'EXT:' . $extensionKey;
        } catch (\RuntimeException $exception) {
            $this->controllerContext->getFlashMessageQueue()->enqueue(
                GeneralUtility::makeInstance(
                    \TYPO3\CMS\Core\Messaging\FlashMessage::class,
                    $exception->getMessage(),
                    '',
                    \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR,
                    true
                )
            );
        }

        // Open freshly created documentation
        $this->redirect('index', null, null, array('reference' => $reference));
    }

    /**
     * Returns the available references.
     *
     * @return array
     */
    protected function getReferences()
    {
        $extensions = $this->extensionRepository->findByHasSphinxDocumentation();
        $references = array();
        foreach ($extensions as $extension) {
            $typeLabel = $this->translate('extensionType_' . $extension->getInstallType());
            $references[$typeLabel]['EXT:' . $extension->getExtensionKey()] = sprintf('[%2$s] %1$s', $extension->getTitle(), $extension->getExtensionKey());
        }

        $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            'afterInitializeReferences',
            array(
                'references' => &$references,
            )
        );

        foreach (array_keys($references) as $key) {
            asort($references[$key]);
        }

        return $references;
    }

    /**
     * Returns the available layouts.
     *
     * @return array
     */
    public function getLayouts()
    {
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
                $renderPdf = true;
                break;
            default:
                $renderPdf = false;
                break;
        }
        if ($renderPdf) {
            $layouts['pdf'] = $this->translate('documentationLayout_pdf');
        }

        return $layouts;
    }

}
