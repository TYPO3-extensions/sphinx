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
	Please visit http://wiki.typo3.org/ReST for further information on reStructuredText.

.. tip::
	This extension automatically installs and configure ``rst2pdf`` to build PDF. However, if you want better output,
	you should consider using LaTeX instead. Please read chapter :ref:`rendering_pdf` for instructions.

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
