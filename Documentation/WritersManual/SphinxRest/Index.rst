.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _sphinx-rest:

Starting with Sphinx and reStructuredText
-----------------------------------------

.. index::
	pair: reStructuredText; Syntax

.. note::
	Don't know how to start with reStructuredText and Sphinx? You may want to check
	this `reStructuredText Primer for TYPO3 Users <https://github.com/xperseguers/TYPO3.docs.rst-primer>`_. This is a
	brief introduction to reStructuredText (reST) concepts and syntax, intended to provide writers with enough
	information to author documents productively.

A few links to get started with Sphinx projects and reStructuredText:

- `reStructuredText Primer for TYPO3 users <https://github.com/xperseguers/TYPO3.docs.rst-primer>`_
- `First Steps with Sphinx <http://sphinx-doc.org/tutorial.html>`_
- `reStructuredText Syntax <http://wiki.typo3.org/ReST_Syntax>`_
- `Information on reStructuredText <http://wiki.typo3.org/ReST>`_


Tips and tricks for PDF rendering
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. index::
	single: Image; Supported formats

PDF rendering on http://docs.typo3.org is using LaTeX as described in chapter :ref:`rendering-pdf`. As such, you should
restrict yourself to using images either as JPG (image/jpeg) or PNG (image/png) and never use images as GIF (image/gif)
which will hinder the PDF generation.
