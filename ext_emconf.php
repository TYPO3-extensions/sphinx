<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "sphinx".
 *
 * Auto generated 19-04-2013 16:05
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Sphinx Python Documentation Generator',
	'description' => 'One-click install Sphinx generator for your TYPO3 website. This extension builds ReStructuredText documentation (format of official TYPO3 manuals).',
	'category' => 'service',
	'author' => 'Xavier Perseguers',
	'author_company' => 'Causal Sàrl',
	'author_email' => 'xavier@causal.ch',
	'shy' => '',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'version' => '0.1.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.0.0-6.1.99',
			'php' => '5.3.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
			'restdoc' => '',
		),
	),
	'_md5_values_when_last_written' => 'a:20:{s:9:"ChangeLog";s:4:"669c";s:20:"class.ext_update.php";s:4:"c2cc";s:21:"ext_conf_template.txt";s:4:"1549";s:12:"ext_icon.gif";s:4:"406f";s:14:"ext_tables.php";s:4:"0fd1";s:6:"README";s:4:"380c";s:32:"Classes/Controller/mod1/conf.php";s:4:"742d";s:33:"Classes/Controller/mod1/index.php";s:4:"2243";s:28:"Classes/EM/Configuration.php";s:4:"bd47";s:33:"Classes/Utility/SphinxBuilder.php";s:4:"72bb";s:36:"Classes/Utility/SphinxQuickstart.php";s:4:"de9a";s:40:"Resources/Private/Language/locallang.xlf";s:4:"7e64";s:44:"Resources/Private/Language/locallang_mod.xlf";s:4:"b82f";s:43:"Resources/Private/Layouts/ModuleSphinx.html";s:4:"6a71";s:53:"Resources/Private/Templates/BlankProject/conf.py.tmpl";s:4:"66db";s:64:"Resources/Private/Templates/BlankProject/MasterDocument.rst.tmpl";s:4:"3895";s:32:"Resources/Public/Css/Backend.css";s:4:"0b18";s:37:"Resources/Public/Images/no-sphinx.png";s:4:"df3f";s:34:"Resources/Public/Images/sphinx.png";s:4:"3a49";s:14:"doc/manual.sxw";s:4:"6859";}',
	'suggests' => array(
	),
);

?>