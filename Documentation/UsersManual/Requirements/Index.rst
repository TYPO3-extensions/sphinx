.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


Requirements
------------

.. index::
	single: Directory; Project structure
	single: Project; Directory structure

This extension requires the Python interpreter to be available on your web server and -- of course -- you need a
documentation written in reStructuredText and as a Sphinx project.

.. tip::
	This extension may automatically install and configure :program:`rst2pdf` to build PDF. However, if you want better
	output, you should consider using LaTeX instead. Please read chapter :ref:`rendering-pdf` for instructions.

The extension supports single directory projects:

.. code-block:: none

	/path/to/project/
	|-- _build
	|-- conf.py
	`-- ...

separate source/build directory projects:

.. code-block:: none

	/path/to/project/
	|-- build
	`-- source
	    |-- conf.py
	    `-- ...

and TYPO3 documentation directory structure:

.. code-block:: none

	/path/to/project/
	|-- ...
	`-- _make
	    |-- build
	    `-- conf.py

The :ref:`dashboard <dashboard>` lets you quickly create new documentation projects based on the layout which fits you
best.
