.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _sphinx-command-line:

Using Sphinx from a command line
--------------------------------

.. index::
	single: Command Line
	single: Symbolic Link

Although this extension is primarily meant at providing a full-fledged environment to build documentation from a TYPO3
backend, it is likely that a few users will use it to easily set up a Sphinx environment on their local machine.

This extension automatically generates shortcut scripts within directory :file:`typo3temp/tx_sphinx/sphinx-doc/bin/`:

.. code-block:: none

	.
	|-- sphinx-build -> sphinx-build-1.2b1
	|-- sphinx-build-1.0.8
	|-- sphinx-build-1.1.3
	|-- sphinx-build-1.2b1
	|-- sphinx-quickstart -> sphinx-quickstart-1.2b1
	|-- sphinx-quickstart-1.0.8
	|-- sphinx-quickstart-1.1.3
	`-- sphinx-quickstart-1.2b1

.. index::
	pair: PATH; Environment Variable

The selected version of Sphinx (script without any version number) is the one you select in the Extension Manager.

.. tip::
	Makefile generally refer to :program:`sphinx-build` to build your documentation. As such, if you plan to use
	Sphinx from the command line, you should consider adding directory :file:`typo3temp/tx_sphinx/sphinx-doc/bin` to
	your ``PATH`` environment variable.
