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

$GLOBALS['LANG']->includeLLFile('EXT:sphinx/Resources/Private/Language/locallang.xlf');
$GLOBALS['BE_USER']->modAccess($GLOBALS['MCONF'], 1);    // This checks permissions and exits if the users has no permission for entry.

/**
 * Module 'Sphinx Console' for the 'sphinx' extension.
 *
 * @category    Backend Module
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class ConsoleController extends \TYPO3\CMS\Backend\Module\BaseScriptClass {

	/** @var string */
	protected $extKey = 'sphinx';

	/** @var \TYPO3\CMS\Core\Utility\File\BasicFileUtility */
	public $basicFF;

	/* @var \TYPO3\CMS\Core\Resource\Folder $folderObject */
	protected $folderObject;

	/** @var string */
	protected $basePath;

	/* @var \TYPO3\CMS\Core\Messaging\FlashMessage $errorMessage */
	protected $errorMessage;

	/** @var \TYPO3\CMS\Extbase\Object\ObjectManager */
	protected $objectManager;

	/** @var array */
	protected $project;

	/**
	 * Initializes the module.
	 *
	 * @return void
	 * @throws \RuntimeException
	 */
	public function init() {
		parent::init();

		$this->id = ($combinedIdentifier = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('id'));
		$this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');

		try {
			if ($combinedIdentifier) {
				/** @var $fileFactory \TYPO3\CMS\Core\Resource\ResourceFactory */
				$fileFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\ResourceFactory');
				$this->folderObject = $fileFactory->getFolderObjectFromCombinedIdentifier($combinedIdentifier);
				// Disallow the rendering of the processing folder (e.g. could be called manually)
				// and all folders without any defined storage
				if ($this->folderObject && ($this->folderObject->getStorage()->getUid() == 0 || trim($this->folderObject->getStorage()->getProcessingFolder()->getIdentifier(), '/') === trim($this->folderObject->getIdentifier(), '/'))) {
					$storage = $fileFactory->getStorageObjectFromCombinedIdentifier($combinedIdentifier);
					$this->folderObject = $storage->getRootLevelFolder();
				}
			} else {
				// Take the first object of the first storage
				$fileStorages = $GLOBALS['BE_USER']->getFileStorages();
				$fileStorage = reset($fileStorages);
				if ($fileStorage) {
					// Validating the input "id" (the path, directory!) and
					// checking it against the mounts of the user. - now done in the controller
					$this->folderObject = $fileStorage->getRootLevelFolder();
				} else {
					throw new \RuntimeException('Could not find any folder to be displayed.', 1349276894);
				}
			}
		} catch (\TYPO3\CMS\Core\Resource\Exception\FolderDoesNotExistException $fileException) {
			// Set folder object to null and throw a message later on
			$this->folderObject = NULL;
			$this->errorMessage = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
				sprintf($GLOBALS['LANG']->getLL('folderNotFoundMessage', TRUE),
					htmlspecialchars($this->id)
				),
				$GLOBALS['LANG']->getLL('folderNotFoundTitle', TRUE),
				\TYPO3\CMS\Core\Messaging\FlashMessage::NOTICE
			);
		}

		// File operation object:
		$this->basicFF = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Utility\\File\\BasicFileUtility');
		$this->basicFF->init($GLOBALS['FILEMOUNTS'], $GLOBALS['TYPO3_CONF_VARS']['BE']['fileExtensions']);
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return void
	 */
	public function main() {
		// Initialize doc
		$this->doc = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('template');
		$this->doc->setModuleTemplate(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('sphinx') . 'Resources/Private/Layouts/ModuleSphinx.html');
		$this->doc->backPath = $GLOBALS['BACK_PATH'];
		$this->doc->styleSheetFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('sphinx') . 'Resources/Public/Css/Backend.css';

		/** @var \TYPO3\CMS\Filelist\FileList $filelist */
		$filelist = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Filelist\FileList');
		$filelist->backPath = $GLOBALS['BACK_PATH'];

		if (!isset($this->MOD_SETTINGS['sort'])) {
			// Set default sorting
			$this->MOD_SETTINGS['sort'] = 'file';
			$this->MOD_SETTINGS['reverse'] = 0;
		}

		$filelist->start($this->folderObject, 0, $this->MOD_SETTINGS['sort'], $this->MOD_SETTINGS['reverse'], $this->MOD_SETTINGS['clipBoard'], $this->MOD_SETTINGS['bigControlPanel']);

		// Generate the list
		$filelist->generateList();
		// Write the footer
		$filelist->writeBottom();

		// Setting up the buttons and markers for docheader
		list($buttons, $markers) = $filelist->getButtonsAndOtherMarkers($this->folderObject);

		// Add the folder info to the marker array
		$markers['FOLDER_INFO'] = $filelist->getFolderInfo();

		$docHeaderButtons = $this->getButtons();

		// Remove unwanted markers
		$markers['CSH'] = '';
		$docHeaderButtons['save'] = '';

		// Draw the form
		$this->doc->form = '<form action="" method="post" enctype="multipart/form-data">';

		$storageRecord = $this->folderObject->getStorage()->getStorageRecord();
		if ($storageRecord['driver'] === 'Local') {
			$this->basePath = PATH_site . $this->folderObject->getPublicUrl();

			if ($_POST['project']) {
				\Causal\Sphinx\Utility\SphinxQuickstart::createProject(
					$this->basePath,
					$_POST['project'],
					$_POST['author'],
					FALSE,
					$_POST['template']
				);
			}
		} else {
			// Not supported
			$this->basePath = '';
		}

		// Render content:
		$this->initializeSphinxProject();
		$this->moduleContent();

		// Compile document
		$markers['CONTENT'] = $this->content;

		// Build the <body> for the module
		$this->content = '';
		$this->content .= $this->doc->startPage($GLOBALS['LANG']->getLL('title'));
		$this->content .= $this->doc->moduleBody($this->pageinfo, $docHeaderButtons, $markers);
		$this->content .= $this->doc->endPage();

		$this->content = $this->doc->insertStylesAndJS($this->content);
	}

	/**
	 * Prints out the module HTML.
	 *
	 * @return void
	 */
	public function printContent() {
		echo $this->content;
	}

	/**
	 * Generates the module content.
	 *
	 * @return void
	 */
	protected function moduleContent() {
		if (!$this->project['initialized']) {
			$this->kickstartFormAction();
		} else {
			$this->buildFormAction();
		}
	}

	/**
	 * Generates a form to kickstart a Sphinx project.
	 *
	 * @return void
	 */
	protected function kickstartFormAction() {
		/** @var $view \TYPO3\CMS\Fluid\View\StandaloneView */
		$view = $this->objectManager->get('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
		$template = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($this->extKey) . 'Resources/Private/Templates/Console/KickstartForm.html';
		$view->setTemplatePathAndFilename($template);
		$this->content .= $view->render();
	}

	/**
	 * Generates a form to build Sphinx projects.
	 *
	 * @return void
	 */
	protected function buildFormAction() {
		// Handle compilation, if needed
		$output = '';
		$operation = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('operation');
		if ($operation) {
			$output = $this->handleCompilation($operation);
		}

		$sphinxVersion = \Causal\Sphinx\Utility\SphinxBuilder::getSphinxVersion();
		$values = array(
			'project' => $this->project,
			'build' => array(
				'sphinxVersion'  => ($sphinxVersion ?: 'n/a'),
				'baseDirectory' => substr($this->project['basePath'], strlen(PATH_site)),
			),
			'disableCompile' => empty($sphinxVersion),
			'hasPdflatex' => \TYPO3\CMS\Core\Utility\CommandUtility::getCommand('pdflatex') !== '',
			'consoleOutput' => $output,
		);

		/** @var $view \TYPO3\CMS\Fluid\View\StandaloneView */
		$view = $this->objectManager->get('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
		$template = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($this->extKey) . 'Resources/Private/Templates/Console/BuildForm.html';
		$view->setTemplatePathAndFilename($template);
		$view->assignMultiple($values);
		$this->content .= $view->render();
	}

	/**
	 * Handles the compilation of a Sphinx project.
	 *
	 * @param string $operation
	 * @return string
	 */
	protected function handleCompilation($operation) {
		switch ($operation) {
			case 'build_html':
				try {
					$output = \Causal\Sphinx\Utility\SphinxBuilder::buildHtml(
						$this->project['basePath'],
						rtrim($this->project['source'], '/'),
						rtrim($this->project['build'], '/'),
						$this->project['conf_py']
					);
				} catch (\RuntimeException $e) {
					$output = $e->getMessage();
				}
				break;
			case 'build_json':
				try {
					$output = \Causal\Sphinx\Utility\SphinxBuilder::buildJson(
						$this->project['basePath'],
						rtrim($this->project['source'], '/'),
						rtrim($this->project['build'], '/'),
						$this->project['conf_py']
					);
				} catch (\RuntimeException $e) {
					$output = $e->getMessage();
				}
				break;
			case 'build_latex':
				try {
					$output = \Causal\Sphinx\Utility\SphinxBuilder::buildLatex(
						$this->project['basePath'],
						rtrim($this->project['source'], '/'),
						rtrim($this->project['build'], '/'),
						$this->project['conf_py']
					);
				} catch (\RuntimeException $e) {
					$output = $e->getMessage();
				}
				break;
			case 'build_pdf':
				try {
					$output = \Causal\Sphinx\Utility\SphinxBuilder::buildPdf(
						$this->project['basePath'],
						rtrim($this->project['source'], '/'),
						rtrim($this->project['build'], '/'),
						$this->project['conf_py']
					);
				} catch (\RuntimeException $e) {
					$output = $e->getMessage();
				}
				break;
			case 'check_links':
				try {
					$output = \Causal\Sphinx\Utility\SphinxBuilder::checkLinks(
						$this->project['basePath'],
						rtrim($this->project['source'], '/'),
						rtrim($this->project['build'], '/'),
						$this->project['conf_py']
					);
				} catch (\RuntimeException $e) {
					$output = $e->getMessage();
				}
				break;
			default:
				$output = '';
				break;
		}

		return $output;
	}

	/**
	 * Initializes the Sphinx project with current directory.
	 *
	 * @return void
	 */
	protected function initializeSphinxProject() {
		if (is_file($this->basePath . 'conf.py')) {
			$this->project['singleDirectory'] = TRUE;
			$this->project['basePath'] = $this->basePath;
			$this->project['source'] = './';
			$this->project['build'] = '_build/';
			$this->project['conf_py'] = './conf.py';
			$this->project['initialized'] = TRUE;
		} elseif (is_file($this->basePath . 'source/conf.py')) {
			$this->project['singleDirectory'] = FALSE;
			$this->project['basePath'] = $this->basePath;
			$this->project['source'] = 'source/';
			$this->project['build'] = 'build/';
			$this->project['conf_py'] = 'source/conf.py';
			$this->project['initialized'] = TRUE;
		} elseif (is_file($this->basePath . '_make/conf.py')) {
			$this->project['singleDirectory'] = FALSE;
			$this->project['basePath'] = $this->basePath;
			$this->project['source'] = './';
			$this->project['build'] = '_make/build/';
			$this->project['conf_py'] = '_make/conf.py';
			$this->project['initialized'] = TRUE;
		} else {
			$this->project['initialized'] = FALSE;
		}

		if ($this->project['initialized']) {
			$properties = \Causal\Sphinx\Utility\Configuration::load($this->basePath . $this->project['conf_py']);
			$this->project['properties'] = $properties;
		}
	}

	/**
	 * Creates the panel of buttons for submitting the form or otherwise perform operations.
	 *
	 * @return array All available buttons as an assoc.
	 */
	protected function getButtons() {
		$buttons = array(
			'csh' => '',
			'shortcut' => '',
			'save' => ''
		);

		// CSH
		$buttons['csh'] = \TYPO3\CMS\Backend\Utility\BackendUtility::cshItem('_MOD_web_func', '', $GLOBALS['BACK_PATH']);

		// SAVE button
		$buttons['save'] = '<input type="image" class="c-inputButton" name="submit" value="Update"' . \TYPO3\CMS\Backend\Utility\IconUtility::skinImg($GLOBALS['BACK_PATH'], 'gfx/savedok.gif', '') . ' title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:rm.saveDoc', 1) . '" />';

		// Shortcut
		if ($GLOBALS['BE_USER']->mayMakeShortcut())    {
			$buttons['shortcut'] = $this->doc->makeShortcutIcon('', 'function', $this->MCONF['name']);
		}

		return $buttons;
	}

}

// Make instance:
/** @var $SOBE \Causal\Sphinx\Controller\ConsoleController */
$SOBE = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Causal\\Sphinx\\Controller\\ConsoleController');
$SOBE->init();

// Include files?
foreach ($SOBE->include_once as $INC_FILE) {
	include_once($INC_FILE);
}

$SOBE->main();
$SOBE->printContent();

?>