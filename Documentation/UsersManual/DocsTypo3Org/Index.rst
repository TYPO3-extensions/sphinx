.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _docs-typo3-org:

Rendering on docs.typo3.org
---------------------------

When you upload your extension to the :abbr:`TER (TYPO3 Extension Repository)`, the associated
Sphinx/reStructuredText-based documentation gets automatically rendered on http://docs.typo3.org under
``http://docs.typo3.org/typo3cms/extensions/<extension-key>/``.

For instance, this documentation gets rendered to http://docs.typo3.org/typo3cms/extensions/sphinx/.

In addition, a zip archive is automatically created for each combination of version and language and contains a copy of
the HTML output (aka "static layout" within this extension) and its PDF counterpart (if activated, see
:ref:`below <docs-typo3-org-pdf>`). Archives are stored within
``http://docs.typo3.org/typo3cms/extensions/<extension-key>/packages/`` ``<extension-key>-<version>-<language>.zip``.
E.g.,

- http://docs.typo3.org/typo3cms/extensions/sphinx/packages/sphinx-1.1.0-default.zip
- http://docs.typo3.org/typo3cms/extensions/sphinx/packages/sphinx-1.1.0-fr-fr.zip

The list of available packages can be seen on http://docs.typo3.org/typo3cms/extensions/sphinx/packages/packages.xml
(you may of course replace segment ``/sphinx/`` with any other extension key).

.. caution::
	Files and URIs are generated lower-case and with dashes instead of underscores. This means that a documentation
	with language (or to be exact *locale*) ``fr_FR`` will be accessible using ``fr-fr`` instead.


Title, copyright and version
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. index::
	single: Settings.yml (format)

A valid Sphinx project for an extension manual should contain a configuration file ``Settings.yml`` next to the main
document ``Index.rst``. This file is your key to override default settings from the real Sphinx configuration file
``conf.py`` which is not part of your project (because it contains settings related to the build environment on
http://docs.typo3.org). Instead, this YAML configuration file lets you define project options.

This extension takes care of loading options from ``Settings.yml`` as well, thus ensuring a smooth experience when
working locally on your extension manuals before their automatic deployment to http://docs.typo3.org.

A basic ``Settings.yml`` file should define a few basic project information:

.. code-block:: yaml
	:linenos:

	conf.py:
	  copyright: 2013
	  project: Sphinx Python Documentation Generator and Viewer
	  version: 1.2
	  release: 1.2.0

project
	The documented project's name.

copyright
	A copyright statement in the style ``2013, Author Name``.

	.. tip::
		Within TYPO3 official documentation, we usually only show the year(s) of copyright, e.g., ``2013``
		or ``2010-2013``.

version
	The major project version, used as the replacement for ``|version|``. For example, for the TYPO3
	documentation, this may be something like ``6.2``.

release
	The full project version, used as the replacement for ``|release|``. For example, for the TYPO3 documentation, this
	may be something like ``6.2.0rc1``.

	If you don't need the separation provided between ``version`` and ``release``, just set them both to
	the same value.

	.. tip::
		This is of course up to the extension's author to decide on a version numbering scheme but best practices follow
		the same rules as for TYPO3 core and do not introduce breaking changes or new features in patch-release
		versions (when the last of the three digits changes).

		As extension authors are very likely to forget to update the version prior to uploading their extension to TER,
		the rendering engine on http://docs.typo3.org automatically overrides the *version* and *release* parameter
		to the actual version as seen on TER.


.. _docs-typo3-org-pdf:

PDF rendering
^^^^^^^^^^^^^

The PDF of your documentation is rendered using the LaTeX builder from Sphinx (see :ref:`rendering-pdf` if needed)
and should be explicitly activated for your extension. To do so, open file ``Settings.yml`` (at the root of your
documentation folder) and make sure it contains following configuration options (lines 6 to 15):

.. code-block:: yaml
	:linenos:

	conf.py:
	  copyright: 2013
	  project: Sphinx Python Documentation Generator and Viewer
	  version: 1.1
	  release: 1.1.0
	  latex_documents:
	  - - Index
	    - sphinx.tex
	    - Sphinx Python Documentation Generator and Viewer
	    - Xavier Perseguers
	    - manual
	  latex_elements:
	    papersize: a4paper
	    pointsize: 10pt
	    preamble: \usepackage{typo3}

Lines 7 to 11 define options for value ``latex_documents`` which determines how to group the document tree into LaTeX
source files. This is a list of tuples: ``startdocname``, ``targetname``, ``title``, ``author``, ``documentclass``, where
the items are:

startdocname
	Document name that is the "root" of the LaTeX files. All documents referenced by it in TOC trees will be included
	in the LaTeX file too.

	.. warning::
		Always use ``Index`` here.

targetname
	File name of the LaTeX file in the output directory.

	.. warning::
		Always use your extension key followed by ``.tex`` here.

title
	LaTeX document title. This is inserted as LaTeX markup, so special characters like a backslash or ampersand must be
	represented by the proper LaTeX commands if they are to be inserted literally.

author
	Author for the LaTeX document. The same LaTeX markup caveat as for *title* applies. Use ``\and`` to separate
	multiple authors, as in: ``'John \and Sarah'``.

documentclass
	Normally, one of ``manual`` or ``howto`` (provided by Sphinx).

	.. tip::
		To keep TYPO3 branding, you should always use ``manual`` here.

Lines 12 to 15 should be kept as-this. Line 15 is actually the "trigger" for PDF rendering.

When activated, your PDF gets automatically rendered on http://docs.typo3.org under
``http://docs.typo3.org/typo3cms/extensions/<extension-key>/_pdf/``. E.g.,
http://docs.typo3.org/typo3cms/extensions/sphinx/_pdf/.

Please read chapter :ref:`customizing-rendering` for further information on LaTeX configuration options.


.. _docs-typo3-org-multilingual:

Multilingual documentation
^^^^^^^^^^^^^^^^^^^^^^^^^^

.. index::
	single: Multilingual manual

Multilingual exension manuals are supported by both this extension and http://docs.typo3.org. If you want to translate
your documentation, kickstart a new Sphinx project (incl. ``Settings.yml``) within directory
``Documentation/Localization.<locale>``.

.. tip::
	You may reuse assets such as ``Includes.txt`` or images from the main documentation under directory
	``Documentation`` but not the other way around, so you cannot reuse assets from a translated manual within the
	main (English) manual.

Locales
"""""""

.. index::
	single: Locales

The list of supported languages for Sphinx is:

======  ========================
Prefix  Name
======  ========================
bn      Bengali
ca      Catalan
cs      Czech
da      Danish
de      German
es      Spanish
et      Estonian
eu      Basque
fa      Iranian
fi      Finnish
fr      French
hr      Croatian
hu      Hungarian
id      Indonesian
it      Italian
ja      Japanese
ko      Korean
lt      Lithuanian
lv      Latvian
mk      Macedonian
nb_NO   Norwegian Bokmal
ne      Nepali
nl      Dutch
pl      Polish
pt_BR   Brazilian Portuguese
ru      Russian
si      Sinhala
sk      Slovak
sl      Slovenian
sv      Swedish
tr      Turkish
uk_UA   Ukrainian
zh_CN   Simplified Chinese
zh_TW   Traditional Chinese
======  ========================

Unless for the few prefixes which are already "locales", http://docs.typo3.org expects a locale and not a language code
to be used; so make sure to extend the prefix accordingly. E.g., a French documentation (prefix ``fr``) should be
extended either to ``fr_FR`` (French France) or ``fr_CA`` (French Canada).

Your translated exension manual will get rendered to http://docs.typo3.org/typo3cms/extensions/sphinx/fr-fr/ (HTML) and
http://docs.typo3.org/typo3cms/extensions/sphinx/fr-fr/_pdf/ (PDF).

.. caution::
	Files and URIs are generated lower-case and with dashes instead of underscores. This means that a documentation
	with locale ``fr_FR`` will be accessible using ``fr-fr`` instead.


.. _docs-typo3-org-crosslink:

Cross-link to other documentation
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

By default, Sphinx on http://docs.typo3.org lets you cross-link to official manuals and thus looking up references in a
foreign set by prefixing the link target appropriately. A link like ``:ref:`stdWrap in details <t3tsref:stdwrap>``` will
create a link to the stable version of the official TYPO3 "TypoScript Reference", within chapter "stdWrap":

* :ref:`stdWrap in details <t3tsref:stdwrap>`

Behind the scenes, this works as follows:

- Each Sphinx HTML build creates a file named ``objects.inv`` that contains a mapping from object names to URIs relative
  to the HTML set’s root.

- Projects using the Intersphinx extension can specify the location of such mapping files in the ``intersphinx_mapping``
  config value. The mapping will then be used to resolve otherwise missing references to objects into links to the other
  documentation.

The list of official manuals and corresponding prefixes may be found on http://docs.typo3.org/typo3cms/Index.html.

.. caution::
	Although Sphinx on http://docs.typo3.org automagically lets you cross-link to official manuals, it is considered
	bad practice to rely on it. It is even planned to change this behavior altogether. As such, please **always**
	explicitly load the references you would like to cross-link to, as explained hereafter.

You may link to any other documentation on http://docs.typo3.org (or elsewhere) by configuring the Intersphinx mapping
within ``Settings.yml``. To do so, add configuration options (lines 6 to 9):

.. code-block:: yaml
	:linenos:

	conf.py:
	  copyright: 2013
	  project: Sphinx Python Documentation Generator and Viewer
	  version: 1.2
	  release: 1.2.0
	  intersphinx_mapping:
	    restdoc:
	    - http://docs.typo3.org/typo3cms/extensions/restdoc/
	    - null

This will register prefix ``restdoc`` and let us link to any chapter of the documentation of extension
*reST Documentation Viewer*. For instance its ChangeLog with
``:ref:`ChangeLog for EXT:restdoc <restdoc:changelog>```. By convention, you should use the extension key as prefix for
other manuals:

* :ref:`ChangeLog for EXT:restdoc <restdoc:changelog>`

.. caution::
	Once you define some Intersphinx mapping within configuration file ``Settings.yml``, it empties the list of
	official manual references. If you want to cross-link to an official documentation as well, make sure to define the
	corresponding mapping as well.

.. tip::
	You may take advantage of this extension's API to fetch and retrieve the list of references from any extension
	rendered on http://docs.typo3.org by invoking method ``getIntersphinxReferences()``:

	.. code-block:: php

		$extensionKey = 'sphinx';
		$references = \Causal\Sphinx\Utility\GeneralUtility::getIntersphinxReferences($extensionKey);
		print_r($references);
