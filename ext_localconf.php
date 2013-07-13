<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$textFileExtensions = explode(',', $GLOBALS['TYPO3_CONF_VARS']['SYS']['textfile_ext']);
if (!in_array('rst', $textFileExtensions)) {
	$textFileExtensions[] = 'rst';
}
if (!in_array('py', $textFileExtensions)) {
	$textFileExtensions[] = 'py';
}
$GLOBALS['TYPO3_CONF_VARS']['SYS']['textfile_ext'] = implode(',', $textFileExtensions);

/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');

$signalSlotDispatcher->connect(
	'TYPO3\\CMS\\Documentation\\Controller\\DocumentController',
	'afterInitializeDocuments',
	'Causal\\Sphinx\\Slots\\SphinxDocumentation',
	'postProcessDocuments'
);

?>