.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _sphinx:

Sphinx and ReStructuredText
^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. index::
	pair: ReStructuredText; Syntax

A few links to get started with Sphinx projects and ReStructuredText:

- `ReStructuredText Syntax <http://wiki.typo3.org/ReST_Syntax>`_


Converting OpenOffice manual to Sphinx
""""""""""""""""""""""""""""""""""""""

The RestTools repository (http://git.typo3.org/Documentation/RestTools.git) provides a script in directory ``T3PythonDocBuilderPackage/src/T3PythonDocBuilder`` to convert your OpenOffice manual to Sphinx/RestructuredText. Please read corresponding ``README`` file for instructions.

If you prefer, you may use an online converter: http://docs.typo3.org/getthedocs/service-convert.html.


Tips and tricks for PDF rendering
"""""""""""""""""""""""""""""""""

.. index::
	single: Image; Supported formats

PDF rendering on http://docs.typo3.org is using LaTeX as described in chapter :ref:`rendering_pdf`. As such, you should restrict yourself to using images either as JPG (image/jpeg) or PNG (image/png).

.. important::

	GIF files (typically coming from a documentation originally written with OpenOffice) are not properly handled by ``pdflatex`` and will prevent your project to be rendered as PDF.
