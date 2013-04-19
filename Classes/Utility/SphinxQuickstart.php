<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Xavier Perseguers <xavier@causal.ch>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * SphinxQuickstart Wrapper.
 *
 * @category    Utility
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @version     SVN: $Id$
 */
class Tx_Sphinx_Utility_SphinxQuickstart {

	/**
	 * Creates a empty Sphinx project.
	 *
	 * @param string $pathRoot
	 * @param string $projectName
	 * @param string $author
	 * @param boolean $separateSourceBuild
	 * @return boolean
	 */
	public function createProject(
		$pathRoot,
		$projectName,
		$author,
		$separateSourceBuild = FALSE
	) {
		$projectName = str_replace("'", ' ', $projectName);

		// Inside the root directory, two more directories will be created; "_templates"
		// for custom HTML templates and "_static" for custom stylesheets and other static
		// files. You can enter another prefix (such as ".") to replace the underscore.
		$namePrefixTemplatesStatic = '_';

		// Sphinx has the notion of a "version" and a "release" for the
		// software. Each version can have multiple releases. For example, for
		// Python the version is something like 2.5 or 3.0, while the release is
		// something like 2.5.1 or 3.0a1.  If you don't need this dual structure,
		// just set both to the same value.
		$version = '1.0';
		$release = '1.0.0';

		// The file name suffix for source files. Commonly, this is either ".txt"
		// or ".rst".  Only files with this suffix are considered documents.
		$sourceFileSuffix = '.rst';

		// One document is special in that it is considered the top node of the
		// "contents tree", that is, it is the root of the hierarchical structure
		// of the documents. Normally, this is "index", but if your "index"
		// document is a custom template, you can also set this to another filename.
		$masterDocument = 'index';

		$pathRoot = rtrim($pathRoot, '/') . '/';
		\TYPO3\CMS\Core\Utility\GeneralUtility::mkdir_deep($pathRoot);

		if ($separateSourceBuild) {
			$directories = array(
				'source/' . $namePrefixTemplatesStatic . 'static',
				'source/' . $namePrefixTemplatesStatic . 'templates',
				'build',
			);
			$files = array(
				'conf.py' => 'source/conf.py',
				'index'   => 'source/' . $masterDocument . $sourceFileSuffix,
			);
		} else {
			$directories = array(
				$namePrefixTemplatesStatic . 'static',
				$namePrefixTemplatesStatic . 'templates',
				'_build',
			);
			$files = array(
				'conf.py' => 'conf.py',
				'index'   => $masterDocument . $sourceFileSuffix,
			);
			$excludePattern = '_build';
		}
		foreach ($directories as $directory) {
			\TYPO3\CMS\Core\Utility\GeneralUtility::mkdir_deep($pathRoot . $directory);
		}

		$now = date('r');
		$year = date('Y');
		$index = <<<EOT
.. $projectName documentation master file, created by
   TYPO3 extension sphinx on $now.
   You can adapt this file completely to your liking, but it should at least
   contain the root `toctree` directive.

Welcome to $projectName's documentation!
=============================================================

Contents:

.. toctree::
   :maxdepth: 2



Indices and tables
==================

* :ref:`genindex`
* :ref:`modindex`
* :ref:`search`
EOT;

		\TYPO3\CMS\Core\Utility\GeneralUtility::writeFile($pathRoot . $files['index'], $index);

		$conf = <<<EOT
# -*- coding: utf-8 -*-
#
# $projectName documentation build configuration file

import sys, os

# -- General configuration -----------------------------------------------------

# Add any Sphinx extension module names here, as strings. They can be extensions
# coming with Sphinx (named 'sphinx.ext.*') or your custom ones.
extensions = []

# Add any paths that contain templates here, relative to this directory.
templates_path = ['{$namePrefixTemplatesStatic}templates']

# The suffix of source filenames.
source_suffix = '$sourceFileSuffix'

# The encoding of source files.
#source_encoding = 'utf-8-sig'

# The master toctree document.
master_doc = '$masterDocument'

# General information about the project.
project = u'$projectName'
copyright = u'$year, $author'

# The version info for the project you're documenting, acts as replacement for
# |version| and |release|, also used in various other places throughout the
# built documents.
#
# The short X.Y version.
version = '$version'
# The full version, including alpha/beta/rc tags.
release = '$release'

# The language for content autogenerated by Sphinx. Refer to documentation
# for a list of supported languages.
#language = None

# There are two options for replacing |today|: either, you set today to some
# non-false value, then it is used:
#today = ''
# Else, today_fmt is used as the format for a strftime call.
#today_fmt = '%B %d, %Y'

# List of patterns, relative to source directory, that match files and
# directories to ignore when looking for source files.
exclude_patterns = ['$excludePattern']

# The name of the Pygments (syntax highlighting) style to use.
pygments_style = 'sphinx'


# -- Options for HTML output ---------------------------------------------------

# The theme to use for HTML and HTML Help pages.  See the documentation for
# a list of builtin themes.
html_theme = 'default'

# Add any paths that contain custom static files (such as style sheets) here,
# relative to this directory. They are copied after the builtin static files,
# so a file named "default.css" will overwrite the builtin "default.css".
html_static_path = ['{$namePrefixTemplatesStatic}static']
EOT;

		\TYPO3\CMS\Core\Utility\GeneralUtility::writeFile($pathRoot . $files['conf.py'], $conf);

		return TRUE;
	}

}

?>