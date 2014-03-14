.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


Requirements
------------

.. index::
	pair: Directory; Structure

This extension requires the Python interpreter to be available on your web server and -- of course -- you need a
documentation written in reStructuredText and as a Sphinx project.

.. note::
	Don't know how to start with reStructuredText and Sphinx? You may want to check
	this `reStructuredText Primer for TYPO3 Users <https://github.com/xperseguers/TYPO3.docs.rst-primer>`_. This is a
	brief introduction to reStructuredText (reST) concepts and syntax, intended to provide writers with enough
	information to author documents productively.

	Please visit http://wiki.typo3.org/ReST for further information on reStructuredText.

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
