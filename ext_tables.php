<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

if (TYPO3_MODE === 'BE') {
	t3lib_extMgm::addModulePath('file_txsphinxM1', t3lib_extMgm::extPath($_EXTKEY) . 'Classes/Controller/mod1');

	t3lib_extMgm::addModule('file', 'txsphinxM1', '', t3lib_extMgm::extPath($_EXTKEY) . 'Classes/Controller/mod1/');
}

?>