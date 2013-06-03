.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt
.. include:: Images.txt


Sphinx documentation viewer
^^^^^^^^^^^^^^^^^^^^^^^^^^^

This extension provides another Backend module under section "Help":

|section_help|

A drop-down menu on top lists all loaded extensions that are featuring a Sphinx-based documentation and lets you quickly show it **locally**:

|viewer_choose_extension|


Internals
"""""""""

As Sphinx-based extension manuals are meant to be rendered on http://docs.typo3.org using the TYPO3 corporate design, they do not provide the general configuration files needed to be rendered locally.

When selecting an extension manual to be shown from the drop-down menu the following process happens:

* If ``typo3conf/Documentation/<extension-key>/html/Index.html`` is found, the viewer loads it right away

Otherwise:

#. An empty Sphinx project is instantiated within ``typo3temp/tx_sphinx/<extension-key>`` and all files from ``EXT:<extension-key>/Documentation`` are copied in this directory

#. The Sphinx project is built as HTML

#. HTML output is copied to ``typo3conf/Documentation/<extension-key>/html/``

#. The viewer loads the main page ``Index.html``

.. tip::
	A checkbox on the right lets you force the extension manual to be recompiled as HTML:

	|checkbox|
