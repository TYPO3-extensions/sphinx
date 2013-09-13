<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

if (TYPO3_MODE === 'BE') {
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModulePath('file_txsphinxM1', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Classes/Controller/mod1');
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('file', 'txsphinxM1', '', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Classes/Controller/mod1/');

	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		'Causal.' . $_EXTKEY,
		'help',
		'documentation',
		'top',
		array(
			'Documentation' => 'index,kickstart,render,convert,createExtensionProject',
			'InteractiveViewer' => 'render,missingRestdoc,outdatedRestdoc',
			'RestEditor' => 'edit,open,save,autocomplete,accordionReferences,updateIntersphinx',
		),
		array(
			'access' => 'user,group',
			'icon' => 'EXT:' . $_EXTKEY . '/ext_icon.gif',
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_mod_documentation.xlf',
		)
	);
}

?>