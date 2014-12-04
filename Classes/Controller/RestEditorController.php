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
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Core\Utility\CommandUtility;
use Causal\Sphinx\Utility\GitUtility;
use Causal\Sphinx\Utility\MiscUtility;

/**
 * ReStructuredText Editor for the 'sphinx' extension.
 *
 * @category    Backend Module
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class RestEditorController extends AbstractActionController {

	/**
	 * @var \Causal\Sphinx\Domain\Repository\DocumentationRepository
	 * @inject
	 */
	protected $documentationRepository;

	// -----------------------------------------------
	// STANDARD ACTIONS
	// -----------------------------------------------

	/**
	 * Edit action.
	 *
	 * @param string $reference Reference of a documentation
	 * @param string $document The document
	 * @param integer $startLine
	 * @return void
	 * @throws \RuntimeException
	 */
	protected function editAction($reference, $document, $startLine = 1) {
		$parts = $this->parseReferenceDocument($reference, $document);
		$contents = file_get_contents($parts['filename']);
		$readOnly = !(is_writable($parts['filename']) && $this->isEditableFiletype($parts['filename']));

		$this->view->assign('reference', $reference);
		$this->view->assign('extensionKey', $parts['extensionKey']);
		$this->view->assign('document', $document);
		$this->view->assign('contents', $contents);
		$this->view->assign('startLine', $startLine);
		$this->view->assign('readOnly', $readOnly ? 'true' : '');
		$this->view->assign('projectPath', $parts['basePath']);
		$this->view->assign('filename', str_replace('\\', '/', substr($parts['filename'], strlen($parts['basePath']) + 1)));

		$buttons = $this->getButtons();
		$this->view->assign('buttons', $buttons);

		$this->view->assign('controller', $this);

		$this->view->assign('typo3_7x', version_compare(TYPO3_branch, '7', '>='));
	}

	// -----------------------------------------------
	// AJAX ACTIONS
	// -----------------------------------------------

	/**
	 * Opens a file and returns its contents.
	 *
	 * @param string $reference Reference of a documentation
	 * @param string $filename The filename (relative to basePath)
	 * @return string Contents of the file
	 */
	public function openAction($reference, $filename) {
		$response = array();
		$response['statusTitle'] = $this->translate('editor.message.open.title');

		try {
			$parts = $this->parseReferenceDocument($reference, '', $filename);

			$response['contents'] = file_get_contents($parts['filename']);
			$response['readOnly'] = !(is_writable($parts['filename']) && $this->isEditableFiletype($parts['filename']));
			$response['status'] = 'success';
		} catch (\RuntimeException $e) {
			$response['status'] = 'error';
			$response['statusText'] = $e->getMessage();
		}

		$this->returnAjax($response);
	}

	/**
	 * Saves the contents and recompiles the whole documentation if needed.
	 *
	 * @param string $reference Reference of a documentation
	 * @param string $filename The filename (relative to basePath)
	 * @param string $contents New contents to be saved
	 * @param boolean $compile
	 * @return void
	 */
	public function saveAction($reference, $filename, $contents, $compile = FALSE) {
		$response = array();
		$response['statusTitle'] = $this->translate('editor.message.save.title');

		try {
			if (!$this->isEditableFiletype($filename)) {
				throw new \RuntimeException('Cannot write file: Invalid file type.', 1380269075);
			}
			$parts = $this->parseReferenceDocument($reference, '', $filename);

			// Strip trailing spaces using explode/implode instead of a multi-line preg_match to be quicker
			$lines = explode(LF, $contents);
			foreach ($lines as &$line) {
				$line = rtrim($line);
			}
			$contents = implode(LF, $lines);

			$success = is_writable($parts['filename']) && GeneralUtility::writeFile($parts['filename'], $contents);
			if (!$success) {
				throw new \RuntimeException(sprintf(
					$this->translate('editor.message.save.failure'),
					$parts['filename']
				), 1370011487);
			}

			if ($compile) {
				$layout = 'json';
				$force = TRUE;
				$outputFilename = NULL;

				switch ($parts['type']) {
					case 'EXT':
						$outputFilename = MiscUtility::generateDocumentation($parts['extensionKey'], $layout, $force, $parts['locale']);
					break;
					case 'USER':
						$outputFilename = NULL;
						$this->signalSlotDispatcher->dispatch(
							'Causal\\Sphinx\\Controller\\DocumentationController',
							'renderUserDocumentation',
							array(
								'identifier' => $parts['identifier'],
								'layout' => $layout,
								'force' => $force,
								'documentationUrl' => &$outputFilename,
							)
						);
					break;
				}
				if (substr($outputFilename, -4) === '.log') {
					throw new \RuntimeException($this->translate('editor.message.compile.failure'), 1370011537);
				}
			}

			$response['status'] = 'success';
			$response['statusText'] = $this->translate('editor.message.save.success');
		} catch (\RuntimeException $e) {
			$response['status'] = 'error';
			$response['statusText'] = $e->getMessage();
		}

		$this->returnAjax($response);
	}

	/**
	 * Moves a file (source) to a given destination.
	 *
	 * @param string $reference
	 * @param string $source
	 * @param string $destination
	 * @return void
	 */
	public function moveAction($reference, $source, $destination) {
		$success = FALSE;
		$parts = $this->parseReferenceDocument($reference, '');

		$source = str_replace('/', DIRECTORY_SEPARATOR, ltrim($source, '/'));
		$destination = str_replace('/', DIRECTORY_SEPARATOR, rtrim($destination, '/') . '/');

		if (is_dir($parts['basePath'])) {
			$sourceFile = $parts['basePath'] . DIRECTORY_SEPARATOR . $source;
			$targetFile = $parts['basePath'] . DIRECTORY_SEPARATOR . ltrim($destination, DIRECTORY_SEPARATOR) . PathUtility::basename($sourceFile);
			if (!(PathUtility::basename($sourceFile) === 'conf.py' || is_file($targetFile))) {
				if ($parts['usingGit']) {
					$sourceFile = substr($sourceFile, strlen($parts['basePath']) + 1);
					$targetFile = substr($targetFile, strlen($parts['basePath']) + 1);
					$success = GitUtility::move($parts['basePath'], $sourceFile, $targetFile);
				} else {
					$success = rename($sourceFile, $targetFile);
				}
			}
		}

		$response = array();
		$response['status'] = $success ? 'success' : 'error';

		$this->returnAjax($response);
	}

	/**
	 * Removes a file/folder.
	 *
	 * @param string $reference
	 * @param string $path
	 * @throws \RuntimeException
	 * @throws \BadFunctionCallException
	 * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
	 * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
	 */
	public function removeAction($reference, $path) {
		$response = array();
		$success = FALSE;

		$path = str_replace('/', DIRECTORY_SEPARATOR, rtrim($path, '/'));
		$parts = $this->parseReferenceDocument($reference, '');

		if (is_dir($parts['basePath'])) {
			$target = $parts['basePath'] . DIRECTORY_SEPARATOR . $path;
			if (is_file($target)) {
				if ($parts['usingGit']) {
					$target = substr($target, strlen($parts['basePath']) + 1);
					$success = GitUtility::remove($parts['basePath'], $target);
				} else {
					$success = @unlink($target);
				}
				if (!$success) {
					$response['statusText'] = $this->translate('editor.action.error.unknownError');
				}
			} else {
				$files = GeneralUtility::getAllFilesAndFoldersInPath(array(), $target . DIRECTORY_SEPARATOR);
				if (count($files) === 0) {
					if ($parts['usingGit']) {
						$target = substr($target, strlen($parts['basePath']) + 1);
						$success = GitUtility::remove($parts['basePath'], $target);
					} else {
						$success = @rmdir($target);
					}
					if (!$success) {
						$response['statusText'] = $this->translate('editor.action.error.unknownError');
					}
				} else {
					$response['statusText'] = $this->translate('editor.action.error.folderIsNotEmpty');
				}
			}
		}

		$response['status'] = $success ? 'success' : 'error';

		$this->returnAjax($response);
	}

	/**
	 * Shows a form to rename a file/folder.
	 *
	 * @param string $reference
	 * @param string $filename
	 * @return void
	 */
	public function renameDialogAction($reference, $filename) {
		$response = array();

		$fileParts = explode('/', rtrim($filename, '/'));

		$this->view->assignMultiple(array(
			'reference' => $reference,
			'filename' => $filename,
			'newName' => end($fileParts),
		));

		$response['status'] = 'success';
		$response['statusText'] = $this->view->render();

		$this->returnAjax($response);
	}

	/**
	 * Actual renaming action.
	 *
	 * @param string $reference
	 * @param string $filename
	 * @param string $newName
	 * @return void
	 */
	public function renameAction($reference, $filename, $newName) {
		$response = array();
		$success = FALSE;

		$parts = $this->parseReferenceDocument($reference, '');
		$fileParts = explode('/', trim($filename, '/'));

		if (empty($newName) || preg_match('#[/?*:;{}\\\\]#', $newName)) {
			$response['statusText'] = $this->translate('editor.action.error.invalidName');
		} elseif (is_dir($parts['basePath'])) {
			$sourceFile = $parts['basePath'] . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $fileParts);
			array_pop($fileParts);
			if (count($fileParts) > 0) {
				$destinationFile = $parts['basePath'] . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $fileParts) . DIRECTORY_SEPARATOR . $newName;
			} else {
				$destinationFile = $parts['basePath'] . DIRECTORY_SEPARATOR . $newName;
			}
			if (!(is_file($destinationFile) || is_dir($destinationFile))) {
				if ($parts['usingGit']) {
					$sourceFile = substr($sourceFile, strlen($parts['basePath']) + 1);
					$destinationFile = substr($destinationFile, strlen($parts['basePath']) + 1);
					$success = GitUtility::move($parts['basePath'], $sourceFile, $destinationFile);
				} else {
					$success = rename($sourceFile, $destinationFile);
				}
				if ($success) {
					$response['statusText'] = implode('/', $fileParts) . '/' . $newName . (is_dir($destinationFile) ? '/' : '');
				} else {
					$response['statusText'] = $this->translate('editor.action.error.cannotRename');
				}
			} else {
				$response['statusText'] = $this->translate('editor.action.error.destinationExists');
			}
		} else {
			$response['statusText'] = $this->translate('editor.action.error.unknownError');
		}

		$response['status'] = $success ? 'success' : 'error';

		$this->returnAjax($response);
	}

	/**
	 * Shows a form to create a file/folder.
	 *
	 * @param string $reference
	 * @param string $type
	 * @param string $path
	 * @return void
	 */
	public function createDialogAction($reference, $type, $path) {
		$response = array();

		$this->view->assignMultiple(array(
			'reference' => $reference,
			'type' => $type,
			'path' => $path,
		));

		$response['status'] = 'success';
		$response['statusText'] = $this->view->render();

		$this->returnAjax($response);
	}

	/**
	 * Actual file creation.
	 *
	 * @param string $reference
	 * @param string $path
	 * @param string $name
	 * @return void
	 */
	public function createFileAction($reference, $path, $name) {
		$this->createFileOrFolder($reference, $path, $name, TRUE);
	}

	/**
	 * Actual folder creation.
	 *
	 * @param string $reference
	 * @param string $path
	 * @param string $name
	 * @return void
	 */
	public function createFolderAction($reference, $path, $name) {
		$this->createFileOrFolder($reference, $path, $name, FALSE);
	}

	/**
	 * File or folder creation.
	 *
	 * @param string $reference
	 * @param string $path
	 * @param string $name
	 * @param bool $isFile
	 * @return void
	 */
	protected function createFileOrFolder($reference, $path, $name, $isFile) {
		$response = array();
		$success = FALSE;

		$path = str_replace('/', DIRECTORY_SEPARATOR, $path);
		$parts = $this->parseReferenceDocument($reference, '');

		if (empty($name) || preg_match('#[/?*:;{}\\\\]#', $name)) {
			$response['statusText'] = $this->translate('editor.action.error.invalidName');
		} elseif (is_dir($parts['basePath'])) {
			$target = $parts['basePath'] . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) . $name;
			if (!(is_file($target) || is_dir($target))) {
				if ($isFile) {
					$success = GeneralUtility::writeFile($target, '');
					if ($parts['usingGit']) {
						$target = substr($target, strlen($parts['basePath']) + 1);
						GitUtility::add($parts['basePath'], $target);
					}
				} else {
					$success = GeneralUtility::mkdir($target);
				}
			} else {
				$response['statusText'] = $this->translate('editor.action.error.destinationExists');
			}
		}

		$response['status'] = $success ? 'success' : 'error';

		$this->returnAjax($response);
	}

	/**
	 * Shows a form to upload files.
	 *
	 * @param string $reference
	 * @param string $path
	 * @return void
	 */
	public function uploadDialogAction($reference, $path) {
		$response = array();

		$fileExtensions = $GLOBALS['TYPO3_CONF_VARS']['BE']['fileExtensions'];
		$this->view->assignMultiple(array(
			'reference' => $reference,
			'path' => $path,
			'allowedExtensions' => $fileExtensions['webspace']['allow'] ?: '*',
			'deniedExtensions' => $fileExtensions['webspace']['deny'],
		));

		$response['status'] = 'success';
		$response['statusText'] = $this->view->render();

		$this->returnAjax($response);
	}

	/**
	 * Handles upload of files.
	 *
	 * @param string $reference
	 * @param string $path
	 * @return void
	 */
	public function uploadAction($reference, $path) {
		$response = array();
		$success = FALSE;

		$path = str_replace('/', DIRECTORY_SEPARATOR, $path);
		$parts = $this->parseReferenceDocument($reference, '');

		if (is_dir($parts['basePath']) && GeneralUtility::isFirstPartOfStr(str_replace(DIRECTORY_SEPARATOR, '/', $parts['basePath']), PATH_site)) {
			$targetDirectory = substr($parts['basePath'] . '/' . str_replace(DIRECTORY_SEPARATOR, '/', $path), strlen(PATH_site));
			$overwriteExistingFiles = FALSE;

			$data = array();
			$namespace = key($_FILES);

			// Register every upload field from the form:
			$this->registerUploadField($data, $namespace, 'files', $targetDirectory);

			// Initializing:
			/** @var \TYPO3\CMS\Core\Utility\File\ExtendedFileUtility $fileProcessor */
			$fileProcessor = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Utility\\File\\ExtendedFileUtility');
			$fileProcessor->init(array(), $GLOBALS['TYPO3_CONF_VARS']['BE']['fileExtensions']);
			$fileProcessor->setActionPermissions(array('addFile' => TRUE));
			$fileProcessor->dontCheckForUnique = $overwriteExistingFiles ? 1 : 0;

			// Actual upload
			$fileProcessor->start($data);
			try {
				$result = $fileProcessor->processData();
				$response['statusText'] = $this->getFlashMessages();
				$success = TRUE;
			} catch (\Exception $e) {
				$response['statusText'] = $e->getMessage();
			}
		} else {
			$response['statusText'] = $this->translate('editor.action.error.invalidUploadDirectory', array($parts['basePath']));
		}

		$response['status'] = $success ? 'success' : 'error';

		$this->returnAjax($response, TRUE);
	}

	/**
	 * Registers an uploaded file for TYPO3 native upload handling.
	 *
	 * @param array &$data
	 * @param string $namespace
	 * @param string $fieldName
	 * @param string $targetDirectory
	 * @return void
	 */
	protected function registerUploadField(array &$data, $namespace, $fieldName, $targetDirectory = '1:/_temp_/') {
		if (!isset($data['upload'])) {
			$data['upload'] = array();
		}
		$counter = count($data['upload']) + 1;

		$keys = array_keys($_FILES[$namespace]);
		foreach ($keys as $key) {
			$_FILES['upload_' . $counter][$key] = $_FILES[$namespace][$key][$fieldName];
		}
		$data['upload'][$counter] = array(
			'data' => $counter,
			'target' => $targetDirectory,
		);
	}

	/**
	 * Returns the default rendered FlashMessages from queue.
	 *
	 * @return string
	 * @see \TYPO3\CMS\Backend\Template\DocumentTemplate::getFlashMessages()
	 */
	protected function getFlashMessages() {
		/** @var $flashMessageService \TYPO3\CMS\Core\Messaging\FlashMessageService */
		$flashMessageService = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessageService');
		/** @var $defaultFlashMessageQueue \TYPO3\CMS\Core\Messaging\FlashMessageQueue */
		$defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
		$flashMessages = $defaultFlashMessageQueue->renderFlashMessages();
		if (!empty($flashMessages)) {
			$flashMessages = '<div id="typo3-messages">' . $flashMessages . '</div>';
		}
		return $flashMessages;
	}

	/**
	 * Returns the project tree.
	 *
	 * @param string $path
	 * @param string $filename
	 * @return string
	 */
	public function projectTreeAction($path, $filename) {
		$this->view->assignMultiple(array(
			'projectPath' => $path,
			'filename' => $filename,
		));
		$out = $this->view->render();
		return $out;
	}

	/**
	 * Autocomplete action to retrieve an documentation key.
	 *
	 * @return void
	 */
	public function autocompleteAction() {
		// no term passed - just exit early with no response
		if (empty($_GET['term'])) exit;
		$q = strtolower($_GET['term']);

		$manuals = $this->documentationRepository->findManualsBySearchTerm($q);

		$this->returnAjax(array_values($manuals));
	}

	/**
	 * Returns the references from the objects.inv index of a given
	 * extension.
	 *
	 * @param string $reference
	 * @param string $remoteUrl
	 * @param boolean $usePrefix
	 * @param boolean $json
	 * @return void|string
	 */
	public function accordionReferencesAction($reference, $remoteUrl = '', $usePrefix = TRUE, $json = TRUE) {
		list($type, $identifier) = explode(':', $reference, 2);
		$objectsInvFilename = '';

		switch ($type) {
			case 'EXT':
				list($prefix, $locale) = explode('.', $identifier, 2);
				$reference = $prefix;
				$prefix = str_replace('_', '', $prefix);
			break;
			case 'USER':
				$path = '';
				$this->signalSlotDispatcher->dispatch(
					'Causal\\Sphinx\\Controller\\InteractiveViewerController',
					'retrieveBasePath',
					array(
						'identifier' => $identifier,
						'path' => &$path,
					)
				);
				$objectsInvFilename = $path . 'objects.inv';
			// NO BREAK here, fallback to default section
			default:
				$locale = '';
				// Use last segment of reference as prefix
				$segments = explode('.', $reference);
				$prefix = end($segments);
			break;
		}

		$references = MiscUtility::getIntersphinxReferences(
			$reference,
			$locale,
			$remoteUrl,
			$objectsInvFilename
		);
		$out = array();

		$lastMainChapter = '';
		foreach ($references as $chapter => $refs) {
			if (is_numeric($chapter)
				|| $chapter === 'genindex' || $chapter === 'genindex.htm'
				|| $chapter === 'py-modindex' || $chapter === 'py-modindex.htm'
				|| $chapter === 'search' || $chapter === 'search.htm') {

				continue;
			}

			list($mainChapter, $_) = explode('/', $chapter, 2);
			if ($mainChapter !== $lastMainChapter) {
				if ($lastMainChapter !== '') {
					$out[] = '</div>';	// End of accordion content panel
				}

				// UpperCamelCase to separate words
				$titleMainChapter = implode(' ', preg_split('/(?=[A-Z])/', $mainChapter));

				$out[] = '<h3><a href="#"'.
						' title="' . htmlspecialchars(sprintf(
						$this->translate('editor.tooltip.references.chapter'), $titleMainChapter))
					. '">' . htmlspecialchars($titleMainChapter) . '</a></h3>';
				$out[] = '<div>';	// Start of accordion content panel
			}

			$out[] = '<h4>' . htmlspecialchars(substr($chapter, strlen($mainChapter))) . '</h4>';
			$out[] = '<ul>';
			foreach ($refs as $ref) {
				$restReference = ':ref:`' . ($usePrefix ? $prefix . ':' : '') . $ref['name'] . '` ';
				$arg1 = '\'' . str_replace(array('\'', '"'), array('\\\'', '\\"'), $restReference) . '\'';
				$arg2 = '\'' . ($usePrefix ? $prefix : '') . '\'';
				$arg3 = '\'' . $remoteUrl . '\'';
				$insertJS = 'EditorInsert(' . $arg1 . ',' . $arg2 . ',' . $arg3 . ');';
				$out[] = '<li><a href="#" title="' . htmlspecialchars($this->translate('editor.tooltip.references.insert')) .
				 '" onclick="' . $insertJS . '">' . htmlspecialchars($ref['title']) . '</a></li>';
			}
			$out[] = '</ul>';

			$lastMainChapter = $mainChapter;
		}
		$out[] = '</div>';	// End of accordion content panel
		$html = implode(LF, $out);

		if (!$json) {
			return $html;
		}

		$this->returnAjax(array('html' => $html));
	}

	/**
	 * Updates Intersphinx mapping by adding a reference to the
	 * documentation of $extensionKey.
	 *
	 * @param string $reference Reference of a documentation
	 * @param string $prefix
	 * @param string $remoteUrl
	 * @return void
	 * @throws \RuntimeException
	 */
	public function updateIntersphinxAction($reference, $prefix, $remoteUrl = '') {
		list($type, $identifier) = explode(':', $reference, 2);

		switch ($type) {
			case 'EXT':
				list($documentationExtension, $locale) = explode('.', $identifier, 2);
				$settingsFilename = MiscUtility::extPath($documentationExtension) .
					'Documentation/' . ($locale ? 'Localization.' . $locale . '/' : '') . 'Settings.yml';
			break;
			default:
				$parts = $this->parseReferenceDocument($reference, '');
				$settingsFilename = $parts['basePath'] . '/Settings.yml';
			break;
		}

		$ret = MiscUtility::addIntersphinxMapping(
			$settingsFilename,
			$prefix,
			$remoteUrl ?: 'http://docs.typo3.org/typo3cms/extensions/' . $prefix
		);

		$response = array();
		$response['statusTitle'] = $this->translate('editor.message.intersphinx.title');

		if ($ret === NULL) {
			$response['status'] = 'success';
			$response['statusText'] = '';
		} elseif ($ret === TRUE) {
			$response['status'] = 'success';
			$response['statusText'] = $this->translate('editor.message.intersphinx.success');
		} else {
			$response['status'] = 'error';
			$response['statusText'] = sprintf(
				$this->translate('editor.message.intersphinx.failure'),
				$settingsFilename
			);
		}

		$this->returnAjax($response);
	}

	// -----------------------------------------------
	// INTERNAL METHODS
	// -----------------------------------------------

	/**
	 * Returns TRUE if the given filename is allowed to be edited.
	 *
	 * @param string $filename
	 * @return boolean
	 */
	protected function isEditableFiletype($filename) {
		$filename = PathUtility::basename($filename);
		if (($pos = strrpos($filename, '.')) !== FALSE) {
			$extension = strtolower(substr($filename, $pos + 1));
		} else {
			$extension = '';
		}
		return empty($extension) || GeneralUtility::inList(
			$GLOBALS['TYPO3_CONF_VARS']['SYS']['textfile_ext'],
			$extension
		);
	}

	/**
	 * Parses a reference and a document and returns the corresponding filename,
	 * the type of reference, its identifier, the extension key (if available)
	 * and the locale (if available).
	 *
	 * @param string $reference
	 * @param string $document
	 * @param string $filename Optional relative filename
	 * @return array
	 * @throws \RuntimeException
	 * @internal Used only by InteractiveViewerController
	 */
	public function parseReferenceDocument($reference, $document, $filename = '') {
		$extensionKey = NULL;
		$locale = NULL;

		list($type, $identifier) = explode(':', $reference, 2);
		switch ($type) {
			case 'EXT':
				list($extensionKey, $locale) = explode('.', $identifier, 2);
				$originalExtensionKey = $extensionKey;
				if (empty($locale)) {
					$lengthSuffixReadme = strlen(\Causal\Sphinx\Domain\Repository\ExtensionRepository::SUFFIX_README);
					if (substr($extensionKey, -$lengthSuffixReadme) === \Causal\Sphinx\Domain\Repository\ExtensionRepository::SUFFIX_README) {
						$extensionKey = substr($extensionKey, 0, -$lengthSuffixReadme);
						$documentationTypes = MiscUtility::DOCUMENTATION_TYPE_README;
					} else {
						$documentationTypes = MiscUtility::getDocumentationTypes($extensionKey);
					}
				} else {
					$documentationTypes = MiscUtility::getLocalizedDocumentationType($extensionKey, $locale);
				}
				switch (TRUE) {
					case $documentationTypes & MiscUtility::DOCUMENTATION_TYPE_SPHINX:
						$basePath = MiscUtility::extPath($extensionKey) . 'Documentation';
					break;
					case $documentationTypes & MiscUtility::DOCUMENTATION_TYPE_README:
						$basePath = MiscUtility::extPath($extensionKey);
					break;
					default:
						throw new \RuntimeException('Unsupported documentation type for extension "' . $extensionKey . '"', 1379086939);
				}
				$filename = $this->getFilename($originalExtensionKey, $document, $filename, $locale);
			break;
			case 'USER':
				$basePath = NULL;
				$slotFilename = NULL;
				$this->signalSlotDispatcher->dispatch(
					__CLASS__,
					'retrieveRestFilename',
					array(
						'identifier' => $identifier,
						'document' => $document,
						'basePath' => &$basePath,
						'filename' => &$slotFilename,
					)
				);
				if ($slotFilename === NULL) {
					throw new \RuntimeException('No slot found to retrieve filename with identifier "' . $identifier . '"', 1371418203);
				}
				if (empty($filename)) {
					$filename = $slotFilename;
				} else {
					$filename = rtrim($basePath, '/') . '/' . $filename;
				}
			break;
			default:
				throw new \RuntimeException('Unknown reference "' . $reference . '"', 1371163472);
		}

		$basePath = realpath($basePath);
		$usingGit = GitUtility::isAvailable() && GitUtility::status($basePath);

		return array(
			'basePath'     => $basePath,
			'filename'     => realpath($filename),
			'type'         => $type,
			'identifier'   => $identifier,
			'extensionKey' => $extensionKey,
			'locale'       => $locale,
			'usingGit'     => $usingGit,
		);
	}

	/**
	 * Returns the ReST filename corresponding to a given document.
	 *
	 * @param string $extensionKey The TYPO3 extension key (possibly with a __README suffix)
	 * @param string $document The document
	 * @param string $filename The relative filename (if given instead of $document)
	 * @param string $locale The locale to use
	 * @return string
	 * @throws \RuntimeException
	 */
	protected function getFilename($extensionKey, $document, $filename, $locale) {
		if (empty($locale)) {
			$lengthSuffixReadme = strlen(\Causal\Sphinx\Domain\Repository\ExtensionRepository::SUFFIX_README);
			if (substr($extensionKey, -$lengthSuffixReadme) === \Causal\Sphinx\Domain\Repository\ExtensionRepository::SUFFIX_README) {
				$extensionKey = substr($extensionKey, 0, -$lengthSuffixReadme);
				$documentationTypes = MiscUtility::DOCUMENTATION_TYPE_README;
			} else {
				$documentationTypes = MiscUtility::getDocumentationTypes($extensionKey);
			}
		} else {
			$documentationTypes = MiscUtility::getLocalizedDocumentationType($extensionKey, $locale);
		}
		switch (TRUE) {
			case $documentationTypes & MiscUtility::DOCUMENTATION_TYPE_SPHINX:
				$path = MiscUtility::extPath($extensionKey);
				if (empty($locale)) {
					$path .= 'Documentation/';
				} elseif (!empty($filename)) {
					// Allow to write in main directory even if working on translation
					$path .= 'Documentation/';
				} else {
					$localizationDirectories = MiscUtility::getLocalizationDirectories($extensionKey);
					$path .= $localizationDirectories[$locale]['directory'] . '/';
				}
				if (!empty($document)) {
					$filename = $path . ($document ? substr($document, 0, -1) : 'Index') . '.rst';
				} else {
					$filename = $path . $filename;
				}
			break;
			case $documentationTypes & MiscUtility::DOCUMENTATION_TYPE_README:
				$path = MiscUtility::extPath($extensionKey);
				$filename = $path . 'README.rst';
			break;
			default:
				throw new \RuntimeException('Unsupported documentation type for extension "' . $extensionKey . '"', 1371117564);
		}

		// Security check
		$path = realpath($path);
		$filename = realpath($filename);
		if (substr($filename, 0, strlen($path)) !== $path) {
			throw new \RuntimeException('Security notice: attempted to access a file outside of extension "' . $extensionKey . '"', 1370011326);
		}

		return $filename;
	}

	/**
	 * Returns the toolbar buttons.
	 *
	 * @return string
	 */
	protected function getButtons() {
		$buttons = array();

		$buttons[] = $this->createToolbarButton(
			'#',
			$this->translate('toolbar.editor.close'),
			't3-icon-actions t3-icon-actions-document t3-icon-document-close',
			'getContentIframe().CausalSphinxEditor.closeEditor()'
		);
		$buttons[] = '&nbsp;';

		$buttons[] = $this->createToolbarButton(
			'#',
			$this->translate('toolbar.editor.save'),
			't3-icon-actions t3-icon-actions-document t3-icon-document-save',
			'getContentIframe().CausalSphinxEditor.save()'
		);
		$buttons[] = $this->createToolbarButton(
			'#',
			$this->translate('toolbar.editor.saveclose'),
			't3-icon-actions t3-icon-actions-document t3-icon-document-save-close',
			'getContentIframe().CausalSphinxEditor.saveAndClose()'
		);

		return implode(' ', $buttons);
	}

}
