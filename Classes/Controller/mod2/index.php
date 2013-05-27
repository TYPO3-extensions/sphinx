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
		$blankUrl = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($this->extKey) . 'Resources/Public/Html/Mod2Blank.html';

		if (isset($_GET['extension'])) {
			$extensionKey = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('extension');
			if ($extensionKey) {
				$documentationUrl = $this->generateDocumentation($extensionKey);
			} else {
				$documentationUrl = $blankUrl;
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
<frameset rows="30,*" frameborder="no" framespacing="0" border="0">
	<frame src="$menuUrl" frameborder="0" noresize="noresize" scrolling="no" />
	<frame name="viewer" src="$blankUrl" frameborder="0" noresize="noresize" />
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
		$format = LF . TAB . TAB . '<option value="%s">%s (%s)</option>';
		foreach ($extensions as $extensionKey => $name) {
			$options .= sprintf(
				$format,
				htmlspecialchars($extensionKey),
				htmlspecialchars($name),
				htmlspecialchars($extensionKey)
			);
		}

		$label = $GLOBALS['LANG']->getLL('showExtensionDocumentation', TRUE);
		$this->content = <<<HTML
<html>
<head>
	<link rel="stylesheet" type="text/css" href="$cssPath/Documentation.css" />
</head>
<body>
<form action="mod.php" method="get" target="viewer">
	<input type="hidden" name="M" value="help_txsphinxM2" />
	<label for="extension">{$label}</label>
	<select id="extension" name="extension" onchange="this.form.submit();">
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

		$metadata = $this->getExtensionMetaData($extensionKey);
		$basePath = PATH_site . 'typo3temp/tx-' . $this->extKey . '/' . $extensionKey;
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
		list($major, $minor, $remaining) = explode('.', $release, 3);
		if (($pos = strpos($minor, '-')) !== FALSE) {
			// $minor ~ '2-dev'
			$minor = substr($minor, 0, $pos);
		}
		$EM_CONF[$_EXTKEY]['version'] = $major . '.' . $minor;
		$EM_CONF[$_EXTKEY]['release'] = $release;

		return $EM_CONF[$_EXTKEY];
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