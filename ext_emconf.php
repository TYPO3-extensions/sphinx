<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "sphinx".
 *
 * Auto generated 27-04-2013 11:42
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
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'version' => '0.3.0-dev',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.0.0-6.2.99',
			'php' => '5.3.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
			'restdoc' => '',
		),
	),
	'_md5_values_when_last_written' => 'a:31:{s:9:"ChangeLog";s:4:"880b";s:20:"class.ext_update.php";s:4:"ca42";s:21:"ext_conf_template.txt";s:4:"1549";s:12:"ext_icon.gif";s:4:"406f";s:17:"ext_localconf.php";s:4:"26d6";s:14:"ext_tables.php";s:4:"0fd1";s:6:"README";s:4:"380c";s:32:"Classes/Controller/mod1/conf.php";s:4:"742d";s:33:"Classes/Controller/mod1/index.php";s:4:"7e0a";s:28:"Classes/EM/Configuration.php";s:4:"bd47";s:33:"Classes/Utility/SphinxBuilder.php";s:4:"8a8d";s:36:"Classes/Utility/SphinxQuickstart.php";s:4:"4e3c";s:40:"Resources/Private/Language/locallang.xlf";s:4:"7e64";s:44:"Resources/Private/Language/locallang_mod.xlf";s:4:"b82f";s:43:"Resources/Private/Layouts/ModuleSphinx.html";s:4:"6a71";s:53:"Resources/Private/Templates/BlankProject/conf.py.tmpl";s:4:"d4c9";s:64:"Resources/Private/Templates/BlankProject/MasterDocument.rst.tmpl";s:4:"d5e9";s:58:"Resources/Private/Templates/TYPO3DocProject/_Inclusion.txt";s:4:"0f0f";s:67:"Resources/Private/Templates/TYPO3DocProject/AdministratorManual.rst";s:4:"0e9e";s:67:"Resources/Private/Templates/TYPO3DocProject/MasterDocument.rst.tmpl";s:4:"5979";s:61:"Resources/Private/Templates/TYPO3DocProject/Settings.yml.tmpl";s:4:"228a";s:58:"Resources/Private/Templates/TYPO3DocProject/UserManual.rst";s:4:"9239";s:74:"Resources/Private/Templates/TYPO3DocProject/Images/IntroductionPackage.png";s:4:"bd5d";s:60:"Resources/Private/Templates/TYPO3DocProject/Images/Typo3.png";s:4:"82b7";s:91:"Resources/Private/Templates/TYPO3DocProject/Images/AdministratorManual/ExtensionManager.png";s:4:"47a4";s:77:"Resources/Private/Templates/TYPO3DocProject/Images/UserManual/BackendView.png";s:4:"7f27";s:62:"Resources/Private/Templates/TYPO3DocProject/_make/conf.py.tmpl";s:4:"cf37";s:32:"Resources/Public/Css/Backend.css";s:4:"7345";s:37:"Resources/Public/Images/no-sphinx.png";s:4:"df3f";s:34:"Resources/Public/Images/sphinx.png";s:4:"3a49";s:14:"doc/manual.sxw";s:4:"9fe2";}',
	'suggests' => array(
	),
);

?>