.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt
.. include:: Images.txt


.. _sphinx:

Sphinx and ReStructuredText
^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. index::
	pair: ReStructuredText; Syntax

A few links to get started with Sphinx projects and ReStructuredText:

- `ReStructuredText Syntax <http://wiki.typo3.org/ReST_Syntax>`_


Tips and tricks for PDF rendering
"""""""""""""""""""""""""""""""""

.. index::
	single: Image; Supported formats

PDF rendering on http://docs.typo3.org is using LaTeX as described in chapter :ref:`rendering_pdf`. As such, you should restrict yourself to using images either as JPG (image/jpeg) or PNG (image/png).

.. important::

	GIF files (typically coming from a documentation originally written with OpenOffice) are not properly handled by ``pdflatex`` and will prevent your project to be rendered as PDF.
