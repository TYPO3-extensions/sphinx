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

A few links to get started with Sphinx projects and reStructuredText:

- `reStructuredText Primer for TYPO3 users <https://github.com/xperseguers/TYPO3.docs.rst-primer>`_
- `First Steps with Sphinx <http://sphinx-doc.org/tutorial.html>`_
- `reStructuredText Syntax <http://wiki.typo3.org/ReST_Syntax>`_


Converting OpenOffice manual to Sphinx
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The RestTools repository (http://git.typo3.org/Documentation/RestTools.git) provides a script in directory
:file:`T3PythonDocBuilderPackage/src/T3PythonDocBuilder` to convert your OpenOffice manual to Sphinx/reStructuredText.
Please read corresponding :file:`README` file for instructions.

If you prefer, you may use an online converter: http://docs.typo3.org/getthedocs/service-convert.html.


Tips and tricks for PDF rendering
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. index::
	single: Image; Supported formats

PDF rendering on http://docs.typo3.org is using LaTeX as described in chapter :ref:`rendering-pdf`. As such, you should
restrict yourself to using images either as JPG (image/jpeg) or PNG (image/png) and never use images as GIF (image/gif)
which will hinder the PDF generation.
