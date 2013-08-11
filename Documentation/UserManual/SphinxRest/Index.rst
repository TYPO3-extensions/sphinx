.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _sphinx-rest:

Sphinx and reStructuredText
---------------------------

.. index::
	pair: reStructuredText; Syntax

A few links to get started with Sphinx projects and reStructuredText:

- `reStructuredText Syntax <http://wiki.typo3.org/ReST_Syntax>`_


Converting OpenOffice manual to Sphinx
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The RestTools repository (http://git.typo3.org/Documentation/RestTools.git) provides a script in directory ``T3PythonDocBuilderPackage/src/T3PythonDocBuilder`` to convert your OpenOffice manual to Sphinx/reStructuredText. Please read corresponding ``README`` file for instructions.

If you prefer, you may use an online converter: http://docs.typo3.org/getthedocs/service-convert.html.


Tips and tricks for PDF rendering
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. index::
	single: Image; Supported formats

PDF rendering on http://docs.typo3.org is using LaTeX as described in chapter :ref:`rendering_pdf`. As such, you should
restrict yourself to using images either as JPG (image/jpeg) or PNG (image/png) and never use images as GIF (image/gif)
which will hinder the PDF generation.
