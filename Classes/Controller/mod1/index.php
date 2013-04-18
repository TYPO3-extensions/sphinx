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

$GLOBALS['LANG']->includeLLFile('EXT:sphinx/Resources/Private/Language/locallang.xml');
require_once($GLOBALS['BACK_PATH'] . 'class.file_list.inc');
$GLOBALS['BE_USER']->modAccess($MCONF, 1);    // This checks permissions and exits if the users has no permission for entry.

/**
 * Module 'Sphinx Console' for the 'sphinx' extension.
 *
 * @category    Backend Module
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @version     SVN: $Id$
 */
class Tx_Sphinx_Controller_Mod1 extends t3lib_SCbase {

	/** @var t3lib_basicFileFunctions */
	public $basicFF;

	protected $pageinfo;

	/** @var array */
	protected $project;

	/**
	 * Initializes the module.
	 *
	 * @return void
	 */
	public function init() {
		parent::init();

		$this->id = t3lib_div::_GP('id');

		// File operation object:
		$this->basicFF = t3lib_div::makeInstance('t3lib_basicFileFunctions');
		$this->basicFF->init($GLOBALS['FILEMOUNTS'], $GLOBALS['TYPO3_CONF_VARS']['BE']['fileExtensions']);
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return void
	 */
	public function main() {
		// Access check!
		// The page will show only if there is a valid page and if this page may be viewed by the user
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id, $this->perms_clause);
		$access = is_array($this->pageinfo) ? TRUE : FALSE;

		// Initialize doc
		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->setModuleTemplate(t3lib_extMgm::extPath('sphinx') . 'Resources/Private/Layouts/ModuleSphinx.html');
		$this->doc->backPath = $GLOBALS['BACK_PATH'];

		/** @var fileList $filelist */
		$filelist = t3lib_div::makeInstance('fileList');
		$filelist->backPath = $GLOBALS['BACK_PATH'];

		$filelist->start($this->id, 0, $this->MOD_SETTINGS['sort'], $this->MOD_SETTINGS['reverse'], $this->MOD_SETTINGS['clipBoard'], $this->MOD_SETTINGS['bigControlPanel']);

		// Generate the list
		$filelist->generateList();

		// Setting up the buttons and markers for docheader
		list($buttons, $markers) = $filelist->getButtonsAndOtherMarkers($this->id);

		// Add the folder info to the marker array
		$markers['FOLDER_INFO'] = $filelist->getFolderInfo();

		$docHeaderButtons = $this->getButtons();

		// Remove unwanted markers
		$markers['CSH'] = '';
		$docHeaderButtons['save'] = '';

		if (($this->id && $access) || ($GLOBALS['BE_USER']->user['admin'] && !$this->id)) {

			// Draw the form
			$this->doc->form = '<form action="" method="post" enctype="multipart/form-data">';

			// Render content:
			$this->initializeSphinxProject();
			$this->moduleContent();
		} else {
			// If no access or if ID == zero
			$this->content .= $this->doc->spacer(10);
		}

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
			$this->content = 'Please select a folder with a Sphinx project.';
			return;
		}

		// Project properties
		$content = array();
		$content[] = '<table>';
		$content[] = '<tr>';
		$content[] = '	<th>Project</td>';
		$content[] = '	<td>' . $this->project['properties']['project'] . '</td>';
		$content[] = '</tr>';
		$content[] = '<tr>';
		$content[] = '	<th>Copyright</td>';
		$content[] = '	<td>' . $this->project['properties']['copyright'] . '</td>';
		$content[] = '</tr>';
		$content[] = '<tr>';
		$content[] = '	<th>Version</td>';
		$content[] = '	<td>' . $this->project['properties']['version'] . '</td>';
		$content[] = '</tr>';
		$content[] = '<tr>';
		$content[] = '	<th>Release</td>';
		$content[] = '	<td>' . $this->project['properties']['release'] . '</td>';
		$content[] = '</tr>';
		$content[] = '</table>';

		$this->content .= $this->doc->section('Project Properties', implode(LF, $content), 0, 1);

		// Build properties
		$content = array();
		$content[] = '<table>';
		$content[] = '<tr>';
		$content[] = '	<th>Base Directory</td>';
		$content[] = '	<td>' . substr($this->project['basePath'], strlen(PATH_site)) . '</td>';
		$content[] = '</tr>';
		$content[] = '<tr>';
		$content[] = '	<th>Source Directory</td>';
		$content[] = '	<td>' . $this->project['source'] . '</td>';
		$content[] = '</tr>';
		$content[] = '<tr>';
		$content[] = '	<th>Build Directory</td>';
		$content[] = '	<td>' . $this->project['build'] . '</td>';
		$content[] = '</tr>';
		$content[] = '</table>';

		$content[] = $this->doc->spacer(10);

		$content[] = '<button type="submit" name="build_html">Build HTML</button>';
		$content[] = '<button type="submit" name="build_json">Build JSON</button>';
		$content[] = '<button type="submit" name="check_links">Check Links</button>';

		$this->content .= $this->doc->section('Build Properties', implode(LF, $content), 0, 1);

		// Console

		switch (TRUE) {
			case isset($_POST['build_html']):
				try {
					$output = Tx_Sphinx_Utility_SphinxBuilder::buildHtml(
						$this->project['basePath'],
						rtrim($this->project['source'], '/'),
						rtrim($this->project['build'], '/')
					);
				} catch (RuntimeException $e) {
					$output = $e->getMessage();
				}
				break;
			case isset($_POST['build_json']):
				try {
					$output = Tx_Sphinx_Utility_SphinxBuilder::buildJson(
						$this->project['basePath'],
						rtrim($this->project['source'], '/'),
						rtrim($this->project['build'], '/')
					);
				} catch (RuntimeException $e) {
					$output = $e->getMessage();
				}
				break;
			case isset($_POST['check_links']):
				try {
					$output = Tx_Sphinx_Utility_SphinxBuilder::checkLinks(
						$this->project['basePath'],
						rtrim($this->project['source'], '/'),
						rtrim($this->project['build'], '/')
					);
				} catch (RuntimeException $e) {
					$output = $e->getMessage();
				}
				break;
			default:
				$output = '';
				break;
		}

		$content = array();
		$content[] = '<textarea style="background-color:#000; color:#fff; height:50em; width:100%;">' . $output . '</textarea>';

		$this->content .= $this->doc->section('Console', implode(LF, $content), 0, 1);
	}

	/**
	 * Initializes the Sphinx project with current directory.
	 *
	 * @return void
	 */
	protected function initializeSphinxProject() {
		if (is_file($this->id . 'conf.py')) {
			$this->project['singleDirectory'] = TRUE;
			$this->project['basePath'] = $this->id;
			$this->project['source'] = './';
			$this->project['build'] = '_build/';
			$this->project['conf.py'] = $this->id . 'conf.py';
			$this->project['initialized'] = TRUE;
		} elseif (is_file($this->id . 'source/conf.py')) {
			$this->project['singleDirectory'] = FALSE;
			$this->project['basePath'] = $this->id;
			$this->project['source'] = 'source/';
			$this->project['build'] = 'build/';
			$this->project['conf.py'] = $this->id . 'source/conf.py';
			$this->project['initialized'] = TRUE;
		} else {
			$this->project['initialized'] = FALSE;
		}

		if ($this->project['initialized']) {
			$conf = file_get_contents($this->project['conf.py']);
			$properties = array();
			preg_replace_callback(
				'/^\s*([^#].*?)\s*=\s*u?\'(.*)\'/m',
				function ($matches) use (&$properties) {
					$properties[$matches[1]] = $matches[2];
				},
				$conf
			);
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
		$buttons['csh'] = t3lib_BEfunc::cshItem('_MOD_web_func', '', $GLOBALS['BACK_PATH']);

		// SAVE button
		$buttons['save'] = '<input type="image" class="c-inputButton" name="submit" value="Update"' . t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'], 'gfx/savedok.gif', '') . ' title="' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:rm.saveDoc', 1) . '" />';

		// Shortcut
		if ($GLOBALS['BE_USER']->mayMakeShortcut())    {
			$buttons['shortcut'] = $this->doc->makeShortcutIcon('', 'function', $this->MCONF['name']);
		}

		return $buttons;
	}

}

// Make instance:
/** @var $SOBE Tx_Sphinx_Controller_Mod1 */
$SOBE = t3lib_div::makeInstance('Tx_Sphinx_Controller_Mod1');
$SOBE->init();

// Include files?
foreach ($SOBE->include_once as $INC_FILE) {
	include_once($INC_FILE);
}

$SOBE->main();
$SOBE->printContent();

?>