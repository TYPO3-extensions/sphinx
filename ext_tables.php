<?php
defined('TYPO3_MODE') || die();

if (TYPO3_MODE === 'BE') {
	$sphinxConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sphinx']);

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
			'icon' => 'EXT:' . $_EXTKEY . '/Resources/Public/Images/module-sphinx.png',
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_mod_documentation.xlf',
		)
	);

    /** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon('extensions-' . $_EXTKEY . '-download',
        \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
        array(
            'source' => 'EXT:' . $_EXTKEY . '/Resources/Public/Images/download.png',
        )
    );
    unset($iconRegistry);
}
