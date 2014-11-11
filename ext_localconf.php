<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$textFileExtensions = explode(',', $GLOBALS['TYPO3_CONF_VARS']['SYS']['textfile_ext']);
$textFileExtensions = array_merge($textFileExtensions, array('json', 'py', 'rst', 'yml'));
$GLOBALS['TYPO3_CONF_VARS']['SYS']['textfile_ext'] = implode(',', array_unique($textFileExtensions));

/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');

// Hook into EXT:documentation
$signalSlotDispatcher->connect(
	'TYPO3\\CMS\\Documentation\\Controller\\DocumentController',
	'afterInitializeDocuments',
	'Causal\\Sphinx\\Slots\\SphinxDocumentation',
	'postProcessDocuments'
);

// Hook into ourselves to post-process LaTeX generated files
$signalSlotDispatcher->connect(
	'Causal\\Sphinx\\Utility\\SphinxBuilder',
	'afterBuildLatex',
	'Causal\\Sphinx\\Slots\\LatexPostProcessor',
	'postprocess'
);

// Hook into ourselves to handle custom projects
$signalSlotDispatcher->connect(
	'Causal\\Sphinx\\Controller\\DocumentationController',
	'afterInitializeReferences',
	'Causal\\Sphinx\\Slots\\CustomProject',
	'postprocessReferences'
);

$signalSlotDispatcher->connect(
	'Causal\\Sphinx\\Controller\\DocumentationController',
	'renderUserDocumentation',
	'Causal\\Sphinx\\Slots\\CustomProject',
	'render'
);

$signalSlotDispatcher->connect(
	'Causal\\Sphinx\\Controller\\InteractiveViewerController',
	'retrieveBasePath',
	'Causal\\Sphinx\\Slots\\CustomProject',
	'retrieveBasePath'
);

$signalSlotDispatcher->connect(
	'Causal\\Sphinx\\Controller\\RestEditorController',
	'retrieveRestFilename',
	'Causal\\Sphinx\\Slots\\CustomProject',
	'retrieveRestFilename'
);
