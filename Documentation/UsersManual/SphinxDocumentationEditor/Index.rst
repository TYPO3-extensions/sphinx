.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt
.. include:: Images.txt


.. _sphinx-documentation-editor:

Sphinx documentation editor
---------------------------

When showing an extension's manual using the :ref:`interactive layout <layouts>`, the standard TYPO3 Backend toolbar shows a pencil icon to let you edit the corresponding chapter:

|

|edit_chapter|


Editing a document
^^^^^^^^^^^^^^^^^^

The pencil icon loads the the "Ace editor" (http://ace.ajax.org/).

This editor lets you quickly update the corresponding chapter and recompile the documentation if you click on toolbar icon "save and close":

|

|save_compile|

.. note::
	The Ace editor currently lacks syntax highlighting for reStructuredText and is configured with Markdown instead.

On the right side, a panel provides a browser of references within your documentation. The references are
grouped by chapter using a accordion widget:

|reference-browser|

At the beginning an input box lets you show the references of any other extension or official manual providing
a reStructuredText/Sphinx-based documentation. Just type an extension key, part of the extension title or some
words from its description and selects it using the autocompletion mechanism.

.. only:: latex or missing_sphinxcontrib_youtube

	Once you have found the reference you are interested in, using it is just a matter of clicking on its name
	to insert it using the proper reStructuredText syntax in your document.

.. only:: html and not missing_sphinxcontrib_youtube

	Once you have found the reference you are interested in, using it is just a matter of clicking on its name
	to insert it using the proper reStructuredText syntax in your document:

	.. youtube:: TShEf6YkREA
		:width: 100%

	|

In case the reference you insert is not coming from your documentation (that is, you are referencing another
chapter or section) but is a cross-reference to another document, the Intersphinx mapping of your configuration
file ``Settings.yml`` will be automatically updated. You may want to read section
:ref:`docs-typo3-org-crosslink` for additional information.
