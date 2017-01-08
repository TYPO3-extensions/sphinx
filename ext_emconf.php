<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "sphinx".
 *
 * Auto generated 11-04-2015 11:35
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
    'title' => 'Sphinx Python Documentation Generator and Viewer',
    'description' => 'Installs a full-fledged Sphinx environment within your TYPO3 website. Builds and renders Sphinx/reStructuredText-based projects such as extension manuals, official reference guides or your own in-house documents as HTML/JSON/PDF either from TYPO3 Backend or from command-line, as you prefer. Features a reStructuredText editor with cross-reference browser and syntax highlighting.',
    'category' => 'module',
    'author' => 'Xavier Perseguers (Causal)',
    'author_company' => 'Causal Sàrl',
    'author_email' => 'xavier@causal.ch',
    'shy' => '',
    'dependencies' => '',
    'conflicts' => '',
    'priority' => '',
    'module' => '',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => 1,
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => 0,
    'lockType' => '',
    'version' => '2.5.0',
    'constraints' => array(
        'depends' => array(
            'php' => '5.5.0-7.1.99',
            'typo3' => '7.6.0-8.99.99',
        ),
        'conflicts' => array(),
        'suggests' => array(
            'restdoc' => '1.6.0-0.0.0',
        ),
    ),
    '_md5_values_when_last_written' => '',
    'suggests' => array(),
    'autoload' => array(
        'psr-4' => array('Causal\\Sphinx\\' => 'Classes')
    ),
);
