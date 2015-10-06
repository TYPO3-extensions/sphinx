<?php
defined('TYPO3_MODE') || die();

if (TYPO3_MODE === 'BE') {
	$sphinxConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sphinx']);

	if (version_compare(TYPO3_version, '6.99.99', '<=')) {
		$moduleIcon = 'ext_icon.png';
	} else {
		$moduleIcon = 'Resources/Public/Images/module-sphinx.png';
	}

	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		'Causal.' . $_EXTKEY,
		'help',
		'documentation',
		'top',
		array(
			'Documentation' => 'index,dashboard,render,convert,createExtensionProject',
			'InteractiveViewer' => 'render,missingRestdoc,outdatedRestdoc',
			'RestEditor' => 'edit,open,save,move,remove,renameDialog,rename,createDialog,createFile,createFolder,' .
				'uploadDialog,upload,' .
				'projectTree,autocomplete,accordionReferences,updateIntersphinx',
			'Ajax' => 'addCustomProject,createCustomProject,editCustomProject,updateCustomProject,removeCustomProject',
		),
		array(
			'access' => 'user,group',
			'icon' => 'EXT:' . $_EXTKEY . '/' . $moduleIcon,
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_mod_documentation.xlf',
		)
	);

	// Register additional sprite icons
	// @link http://blog.tolleiv.de/2010/07/typo3-4-4-sprites-in-your-extension/
	$icons = array(
		'download'   => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Images/download.png',
	);
	\TYPO3\CMS\Backend\Sprite\SpriteManager::addSingleIcons($icons, $_EXTKEY);
}
