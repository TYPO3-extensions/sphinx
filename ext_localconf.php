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

?>