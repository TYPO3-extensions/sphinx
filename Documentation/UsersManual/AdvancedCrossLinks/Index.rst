.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _advanced-cross-links:

Advanced cross-links
--------------------

.. index::
	single: Intersphinx

.. admonition:: Disclaimer
	:class: caution

	This is an advanced topic that you should skip unless you want to enable cross-link to virtually any website.

This section first describes the format of index file ``objects.inv`` being used by Intersphinx_ and then shows how to
automatically generate such a file for a Doxygen_-based API documentation.

.. _Intersphinx: http://sphinx-doc.org/ext/intersphinx.html

.. _Doxygen: www.doxygen.org/


Format of ``objects.inv``
^^^^^^^^^^^^^^^^^^^^^^^^^

.. index::
	single: objects.inv (format)

An index file ``objects.inv`` consists of two parts; one in plain text, at the beginning, followed by a ZLIB-compressed
list of references::

	# Sphinx inventory version 2
	# Project: My project
	# Version: 1.0.0
	# The remainder of this file is compressed using zlib.
	... zlib-compressed content ...

The first line is checked by Intersphinx to ensure the file has correct format. We only describe version 2 of this index
format.

The ZLIB-compressed list of reference has following structure:

.. code-block:: none

	<anchor name #1> std:label -1 <target url #1> <title of the anchor #1>
	<anchor name #2> std:label -1 <target url #2> <title of the anchor #2>
	<anchor name #3> std:label -1 <target url #3> <title of the anchor #3>

<anchor name>
	Lower-case name of the anchor. You may use special characters such as "-" (dash), "_" (underscore) and "\\"
	(backslash) to separate words.

<target url>
	Relative URL to the page, ended either by:

	- ``#``: to point to the top of the page (e.g., ``admonitions.html#``)
	- ``#$``: to point to the page and append ``<anchor name>`` (as instructed by ``$``)
	- ``#some-anchor``: to point to an arbitrary anchor in the target page (e.g., ``admonitions.html#0145384da``)

<title of the anchor>
	Default title to be used when cross-link does not include an alternative title:

	.. code-block:: restructuredtext

		Automatic title: :ref:`prefix:my-anchor`
		Alternative title: :ref:`My alternative title <prefix:my-anchor>`

.. warning::
	Make sure the last entry of the ZLIB-compressed content ends with a trailing linefeed as well.


Doxygen documentation
^^^^^^^^^^^^^^^^^^^^^

.. index::
	single: Doxygen

What is Doxygen?

	Doxygen is the de facto standard tool for generating documentation from annotated C++ sources, but it also supports
	other popular programming languages such as C, Objective-C, C#, PHP, Java, Python, IDL (Corba, Microsoft, and
	UNO/OpenOffice flavors), Fortran, VHDL, Tcl, and to some extent D.

	-- Dimitri van Heesch


The goal of cross-linking to Doxygen is to be able to write something like that in a developer document:

.. code-block:: restructuredtext

	Please see class :ref:`t3cmsapi:TYPO3\\CMS\\Core\\Utility\\GeneralUtility`
	for details.

	When instantiating a class from a TYPO3 extension, you should not use PHP keyword
	``new`` but call :ref:`t3cmsapi:TYPO3\\CMS\\Core\\Utility\\GeneralUtility::makeInstance`
	instead.

Expected result is:

	Please see class :ref:`t3cmsapi:TYPO3\\CMS\\Core\\Utility\\GeneralUtility`
	for details.

	When instantiating a class from a TYPO3 extension, you should not use PHP keyword
	``new`` but call :ref:`t3cmsapi:TYPO3\\CMS\\Core\\Utility\\GeneralUtility::makeInstance`
	instead.

The problem with Doxygen is that it generates cryptic file names which are hard to link to manually. Fortunately we
found a way to prepare an index file ``objects.inv`` by parsing the XML output of an API documentation.

You need both the HTML and the XML versions of the documentation. Getting XML output is just a matter of adding::

	GENERATE_XML   =   YES

to your Doxygen configuration file. Then generate the API documentation as usual. You should end up with a ``html`` and
a ``xml`` output directories (with default settings).

Then run following script:

.. literalinclude:: prepare-objects-inv.sh
	:language: bash

.. caution::
	Dependencies for this script are:

	* xmlstarlet_
	* PHP CLI

.. _xmlstarlet: http://xmlstar.sourceforge.net/

The index file ``objects.inv`` will be stored along your HTML documentation. You should then deploy this HTML directory
to your website and :ref:`cross-link to it as usual <docs-typo3-org-crosslink>`.
