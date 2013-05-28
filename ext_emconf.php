<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "sphinx".
 *
 * Auto generated 28-05-2013 11:51
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
	'version' => '0.4.0',
	'constraints' => array(
		'depends' => array(
			'php' => '5.3.3-0.0.0',
			'typo3' => '6.0.0-6.1.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
			'restdoc' => '',
		),
	),
	'_md5_values_when_last_written' => 'a:92:{s:9:"ChangeLog";s:4:"1f28";s:20:"class.ext_update.php";s:4:"36ac";s:21:"ext_conf_template.txt";s:4:"a2e3";s:12:"ext_icon.gif";s:4:"406f";s:17:"ext_localconf.php";s:4:"26d6";s:14:"ext_tables.php";s:4:"7fcc";s:32:"Classes/Controller/mod1/conf.php";s:4:"3255";s:33:"Classes/Controller/mod1/index.php";s:4:"fc90";s:32:"Classes/Controller/mod2/conf.php";s:4:"0244";s:33:"Classes/Controller/mod2/index.php";s:4:"7438";s:28:"Classes/EM/Configuration.php";s:4:"1bdc";s:33:"Classes/Utility/Configuration.php";s:4:"4676";s:25:"Classes/Utility/Setup.php";s:4:"e59f";s:33:"Classes/Utility/SphinxBuilder.php";s:4:"390f";s:36:"Classes/Utility/SphinxQuickstart.php";s:4:"555b";s:26:"Documentation/Includes.txt";s:4:"1d66";s:23:"Documentation/Index.rst";s:4:"890d";s:26:"Documentation/Settings.yml";s:4:"4c19";s:38:"Documentation/Administration/Index.rst";s:4:"fe07";s:59:"Documentation/Administration/InstallingExtension/Images.txt";s:4:"8ee7";s:58:"Documentation/Administration/InstallingExtension/Index.rst";s:4:"d465";s:61:"Documentation/Administration/UsingSphinxCommandLine/Index.rst";s:4:"eba6";s:33:"Documentation/ChangeLog/Index.rst";s:4:"804a";s:41:"Documentation/Images/build_button_pdf.png";s:4:"c7a0";s:38:"Documentation/Images/build_buttons.png";s:4:"7549";s:33:"Documentation/Images/checkbox.png";s:4:"043c";s:37:"Documentation/Images/em_configure.png";s:4:"5713";s:34:"Documentation/Images/em_update.png";s:4:"d682";s:38:"Documentation/Images/import_sphinx.png";s:4:"0db8";s:38:"Documentation/Images/mod1_overview.png";s:4:"53bc";s:43:"Documentation/Images/project_properties.png";s:4:"c352";s:39:"Documentation/Images/project_wizard.png";s:4:"ff70";s:48:"Documentation/Images/project_wizard_overview.png";s:4:"cec4";s:37:"Documentation/Images/section_help.png";s:4:"3529";s:39:"Documentation/Images/sphinx_version.png";s:4:"890c";s:31:"Documentation/Images/viewer.png";s:4:"f70e";s:48:"Documentation/Images/viewer_choose_extension.png";s:4:"e7b9";s:36:"Documentation/Introduction/Index.rst";s:4:"50e8";s:49:"Documentation/Introduction/Screenshots/Images.txt";s:4:"41a9";s:48:"Documentation/Introduction/Screenshots/Index.rst";s:4:"8e9b";s:49:"Documentation/Introduction/WhatDoesItDo/Index.rst";s:4:"f525";s:37:"Documentation/KnownProblems/Index.rst";s:4:"aa53";s:32:"Documentation/ToDoList/Index.rst";s:4:"b5f7";s:34:"Documentation/UserManual/Index.rst";s:4:"dd17";s:70:"Documentation/UserManual/BuildingSphinxDocumentationProject/Images.txt";s:4:"3564";s:69:"Documentation/UserManual/BuildingSphinxDocumentationProject/Index.rst";s:4:"95ee";s:70:"Documentation/UserManual/CreatingSphinxDocumentationProject/Images.txt";s:4:"cf90";s:69:"Documentation/UserManual/CreatingSphinxDocumentationProject/Index.rst";s:4:"283e";s:47:"Documentation/UserManual/Requirements/Index.rst";s:4:"17fc";s:61:"Documentation/UserManual/SphinxDocumentationViewer/Images.txt";s:4:"f8ba";s:60:"Documentation/UserManual/SphinxDocumentationViewer/Index.rst";s:4:"5735";s:40:"Resources/Private/Language/locallang.xlf";s:4:"6f8c";s:45:"Resources/Private/Language/locallang_mod1.xlf";s:4:"5fb9";s:45:"Resources/Private/Language/locallang_mod2.xlf";s:4:"677d";s:43:"Resources/Private/Layouts/ModuleSphinx.html";s:4:"6a71";s:50:"Resources/Private/Templates/Console/BuildForm.html";s:4:"bd3d";s:54:"Resources/Private/Templates/Console/KickstartForm.html";s:4:"ee9b";s:62:"Resources/Private/Templates/Projects/BlankProject/conf.py.tmpl";s:4:"d4c9";s:63:"Resources/Private/Templates/Projects/BlankProject/Makefile.tmpl";s:4:"a63f";s:73:"Resources/Private/Templates/Projects/BlankProject/MasterDocument.rst.tmpl";s:4:"d5e9";s:75:"Resources/Private/Templates/Projects/TYPO3DocEmptyProject/Settings.yml.tmpl";s:4:"228a";s:76:"Resources/Private/Templates/Projects/TYPO3DocEmptyProject/_make/conf.py.tmpl";s:4:"cf37";s:77:"Resources/Private/Templates/Projects/TYPO3DocEmptyProject/_make/make-html.bat";s:4:"6d1c";s:72:"Resources/Private/Templates/Projects/TYPO3DocEmptyProject/_make/make.bat";s:4:"9890";s:77:"Resources/Private/Templates/Projects/TYPO3DocEmptyProject/_make/Makefile.tmpl";s:4:"a63f";s:90:"Resources/Private/Templates/Projects/TYPO3DocEmptyProject/_make/_not_versioned/_.gitignore";s:4:"829c";s:67:"Resources/Private/Templates/Projects/TYPO3DocProject/_Inclusion.txt";s:4:"0f0f";s:76:"Resources/Private/Templates/Projects/TYPO3DocProject/AdministratorManual.rst";s:4:"0e9e";s:76:"Resources/Private/Templates/Projects/TYPO3DocProject/MasterDocument.rst.tmpl";s:4:"5979";s:70:"Resources/Private/Templates/Projects/TYPO3DocProject/Settings.yml.tmpl";s:4:"228a";s:67:"Resources/Private/Templates/Projects/TYPO3DocProject/UserManual.rst";s:4:"9239";s:83:"Resources/Private/Templates/Projects/TYPO3DocProject/Images/IntroductionPackage.png";s:4:"bd5d";s:69:"Resources/Private/Templates/Projects/TYPO3DocProject/Images/Typo3.png";s:4:"82b7";s:100:"Resources/Private/Templates/Projects/TYPO3DocProject/Images/AdministratorManual/ExtensionManager.png";s:4:"47a4";s:86:"Resources/Private/Templates/Projects/TYPO3DocProject/Images/UserManual/BackendView.png";s:4:"7f27";s:71:"Resources/Private/Templates/Projects/TYPO3DocProject/_make/conf.py.tmpl";s:4:"cf37";s:72:"Resources/Private/Templates/Projects/TYPO3DocProject/_make/make-html.bat";s:4:"6d1c";s:67:"Resources/Private/Templates/Projects/TYPO3DocProject/_make/make.bat";s:4:"9890";s:72:"Resources/Private/Templates/Projects/TYPO3DocProject/_make/Makefile.tmpl";s:4:"a63f";s:85:"Resources/Private/Templates/Projects/TYPO3DocProject/_make/_not_versioned/_.gitignore";s:4:"829c";s:32:"Resources/Public/Css/Backend.css";s:4:"4157";s:38:"Resources/Public/Css/Documentation.css";s:4:"2593";s:36:"Resources/Public/Html/Mod2Blank.html";s:4:"f714";s:32:"Resources/Public/Images/book.png";s:4:"8007";s:39:"Resources/Public/Images/check_links.png";s:4:"6f39";s:47:"Resources/Public/Images/file_extension_html.png";s:4:"6d8e";s:47:"Resources/Public/Images/file_extension_json.png";s:4:"d131";s:46:"Resources/Public/Images/file_extension_pdf.png";s:4:"95b5";s:46:"Resources/Public/Images/file_extension_tex.png";s:4:"fa1b";s:37:"Resources/Public/Images/no-sphinx.png";s:4:"df3f";s:34:"Resources/Public/Images/sphinx.png";s:4:"3a49";s:14:"doc/manual.sxw";s:4:"6930";}',
	'suggests' => array(
	),
);

?>