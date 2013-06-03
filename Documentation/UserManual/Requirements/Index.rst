.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


Requirements
^^^^^^^^^^^^

.. index::
	pair: Directory; Structure

This extension requires the Python interpreter to be available on your web server and -- of course -- you need a documentation written in reStructuredText and as a Sphinx project.

.. note::
	Please visit http://wiki.typo3.org/ReST for further information on reStructuredText.

.. important::
	If you plan to build PDF, you will need additionally commands ``make`` and ``pdflatex``.

The extension supports single directory projects:

.. code-block:: no-highlight

	/path/to/project/
	|-- _build
	|-- conf.py
	`-- ...

separate source/build directory projects:

.. code-block:: no-highlight

	/path/to/project/
	|-- build
	`-- source
	    |-- conf.py
	    `-- ...

and TYPO3 documentation directory structure:

.. code-block:: no-highlight

	/path/to/project/
	|-- ...
	`-- _make
	    |-- build
	    `-- conf.py
