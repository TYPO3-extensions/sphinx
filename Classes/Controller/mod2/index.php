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

$GLOBALS['LANG']->includeLLFile('EXT:sphinx/Resources/Private/Language/locallang.xlf');
$GLOBALS['BE_USER']->modAccess($GLOBALS['MCONF'], 1);    // This checks permissions and exits if the users has no permission for entry.

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
class DocumentationController extends \TYPO3\CMS\Backend\Module\BaseScriptClass {

	/** @var string */
	protected $extKey = 'sphinx';

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return void
	 */
	public function main() {
		if (isset($_GET['extension'])) {
			$extensionKey = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('extension');
			if ($extensionKey) {
				$documentationUrl = $this->generateDocumentation($extensionKey);
			} else {
				$documentationUrl = 'about::blank';
			}

			$this->content = <<<HTML
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Frameset//EN" "http://www.w3.org/TR/REC-html40/frameset.dtd">
<html>
<frameset rows="*">
	<frame src="$documentationUrl" />
</frameset>
</html>
HTML;
		} elseif (\TYPO3\CMS\Core\Utility\GeneralUtility::_GET('mode') === 'menu') {
			$this->generateMenu();
		} else {
			$menuUrl = 'mod.php?M=help_txsphinxM2&amp;mode=menu';

			$this->content = <<<HTML
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Frameset//EN" "http://www.w3.org/TR/REC-html40/frameset.dtd">
<html>
<frameset rows="25,*" frameborder="no" framespacing="0" border="0">
	<frame src="$menuUrl" frameborder="0" noresize="noresize" />
	<frame name="viewer" src="about::blank" frameborder="0" noresize="noresize" />
</frameset>
</html>
HTML;
		}
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
	 * Generates the menu of documentation.
	 *
	 * @return void
	 */
	protected function generateMenu() {
		$cssPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($this->extKey) . 'Resources/Public/Css/';

		$options = '';
		$extensions = $this->getExtensionsWithSphinxDocumentation();
		foreach ($extensions as $e) {
			$options .= LF . TAB . TAB . '<option value="' . htmlspecialchars($e) . '">' . htmlspecialchars($e) . '</option>';
		}

		$this->content = <<<HTML
<html>
<head>
	<link rel="stylesheet" type="text/css" href="$cssPath/Documentation.css" />
</head>
<body>
<form action="mod.php" method="get" target="viewer">
	<input type="hidden" name="M" value="help_txsphinxM2" />
	Documentation:
	<select name="extension" onchange="this.form.submit();">
		<option value=""></option>
		$options
	</select>
</form>
</body>
</html>
HTML;

	}

	/**
	 * Generates the documentation for a given extension.
	 *
	 * @param string $extensionKey
	 * @return string
	 * @todo Cleanup and output error message in the frame
	 */
	protected function generateDocumentation($extensionKey) {
		$outputDirectory = PATH_site . 'typo3conf/Documentation/' . $extensionKey . '/html';
		if (is_file($outputDirectory . '/Index.html')) {
			// Do not render the documentation again
			// TODO: detect if it is needed anyway

			$documentationUrl = '../' . substr($outputDirectory, strlen(PATH_site)) . '/Index.html';
			return $documentationUrl;
		}

		$basePath = PATH_site . 'typo3temp/tx-' . $this->extKey . '/' . $extensionKey;
		\TYPO3\CMS\Core\Utility\GeneralUtility::rmdir($basePath, TRUE);
		\Causal\Sphinx\Utility\SphinxQuickstart::createProject(
			$basePath,
			$extensionKey,
			'Unknown Author',
			FALSE,
			'TYPO3DocEmptyProject'
		);

		// Recursively instantiate template files
		$source = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extensionKey) . 'Documentation';
		if (!is_dir($source)) {
			throw new \RuntimeException('Documentation directory was not found: ' . $source, 1369679343);
		}
		$this->recursiveCopy($source, $basePath);

		try {
			\Causal\Sphinx\Utility\SphinxBuilder::buildHtml(
				$basePath,
				'.',
				'_make/build',
				'_make/conf.py'
			);
		} catch (\RuntimeException $e) {
			$output = $e->getMessage();
		}

		\TYPO3\CMS\Core\Utility\GeneralUtility::rmdir($outputDirectory, TRUE);
		\TYPO3\CMS\Core\Utility\GeneralUtility::mkdir_deep($outputDirectory . '/');
		$this->recursiveCopy($basePath . '/_make/build/html', $outputDirectory);

		$documentationUrl = '../' . substr($outputDirectory, strlen(PATH_site)) . '/Index.html';
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
	 * Generates the module content.
	 *
	 * @return void
	 */
	protected function moduleContent() {
		$extensions = $this->getExtensionsWithSphinxDocumentation();

		$this->content .= '<iframe style="height:100%" src="../typo3conf/deprecation_d2cd4e1595.log" />';

		$this->content .= var_export($extensions, TRUE);
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
				$extensions[] = $loadedExtension;
			}
		}
		return $extensions;
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
		$buttons['save'] = '';

		// Shortcut
		if ($GLOBALS['BE_USER']->mayMakeShortcut()) {
			$buttons['shortcut'] = $this->doc->makeShortcutIcon('', 'function', $this->MCONF['name']);
		}

		return $buttons;
	}

}

// Make instance:
/** @var $SOBE \Causal\Sphinx\Controller\DocumentationController */
$SOBE = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Causal\\Sphinx\\Controller\\DocumentationController');
$SOBE->init();

// Include files?
foreach ($SOBE->include_once as $INC_FILE) {
	include_once($INC_FILE);
}

$SOBE->main();
$SOBE->printContent();

?>