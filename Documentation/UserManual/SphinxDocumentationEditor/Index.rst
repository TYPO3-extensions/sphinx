.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt
.. include:: Images.txt


.. _sphinx-documentation-editor:

Sphinx documentation editor
^^^^^^^^^^^^^^^^^^^^^^^^^^^

When showing an extension's manual using the :ref:`interactive layout <layouts>`, the standard TYPO3 Backend toolbar shows a pencil icon to let you edit the corresponding chapter:

|edit_chapter|


Editing a document
""""""""""""""""""

The pencil icon loads the online version from the "Ace editor" (http://ace.ajax.org/). Thus it currently requires an active Internet connection and prevents you from offline-editing documentation.

This editor lets you quickly update the corresponding chapter and recompile the documentation if you click on toolbar icon "save and close":

|save_compile|

.. note::
	The Ace editor currently lacks syntax highlighting for ReStructuredText and is configured with Markdown instead.
