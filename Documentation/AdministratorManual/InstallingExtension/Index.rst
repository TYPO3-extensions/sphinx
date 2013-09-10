.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt
.. include:: Images.txt


Installing the extension
------------------------

There are a few steps necessary to install the Sphinx Python Documentation Generator extension. If you have installed other extensions in the past, you will run into little new here.

.. note::
	**MS Windows Users:** Please set up your environment with Python first. Instructions are available as :ref:`a separated chapter <windows-setup>`.


Installing the extension from Extension Manager
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

The Sphinx Python Documentation Generator extension can be installed through the typical TYPO3 installation process using the Extension Manager.


.. _configure-sphinx:

Downloading and configuring Sphinx
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

In the Extension Manager, execute the update script this extension is providing:

|em_update|

Select a version of Sphinx you would like to use and start the import process with the "import" button:

|import_sphinx|

.. important::
	If the list of available versions of Sphinx is empty, you most probably lack OpenSSL support in PHP (this is a typical pitfall under MS Windows).

.. index::
	single: Install; Log
	single: Install; Python Imaging Library
	single: Install; rst2pdf
	single: Install; PyYAML
	single: Install; Pygments
	single: Install; TYPO3 ReST Tools

Everything should work out-of-the-box. Possible problems will be reported as Flash messages and a log of all operations is stored as ``typo3temp/tx_sphinx/IMPORT-<date>.log``. The general process of importing Sphinx is as follows:

#. Fetch the version as a zip archive from https://bitbucket.org/birkenfeld/sphinx/downloads ("Tags") into
   directory ``typo3temp/``

#. Unpack the zip archive into directory ``EXT:sphinx/Resources/Private/sphinx-sources/<version>/``

#. Build the Python libraries into directory ``EXT:sphinx/Resources/Private/sphinx/<version>/``

#. *[Not on MS Windows, other OS : if activated]* Install Python Imaging Library (https://pypi.python.org/pypi/PIL), needed for supporting common image types with ``rst2pdf``

#. *[Not on MS Windows, other OS : if activated]* Install ``rst2pdf`` (http://rst2pdf.ralsina.com.ar/), as a simple way of building PDF

#. Install PyYAML library (http://pyyaml.org/wiki/PyYAML), needed for building TYPO3 documentation

#. Install Pygments library (http://pygments.org/), and configure TypoScript highlighting

#. Install TYPO3-related commands provided by the TYPO3 Documentation Team (TYPO3 ReST tools)

#. Fetch 3rd-party plugins for Sphinx: https://bitbucket.org/birkenfeld/sphinx-contrib/

The manual process buttons let you locally change files and rebuild your environment. This is particularly useful if you want to use the `git repository of the TYPO3 ReST tools`_ instead of a snapshot.

The "download" button fetches the corresponding sources of Sphinx, the TYPO3-related commands, the PyYAML library, the
Pygments library, ... if they are not available locally.

.. important::
	It is known that the Python Imaging Library and/or ``rst2pdf`` might fail to be successfully installed and configured on some systems. However, as these libraries are only used to render PDF with ``rst2pdf`` and as the recommended method for rendering PDF is to use :ref:`LaTeX <rendering-pdf>` anyway, you should not worry if you are unable to install ``rst2pdf`` locally.

.. tip::
	Instead of fetching once for all the TYPO3-related commands, you may prefer to clone the official git repository. To do so, open a terminal and run:

	.. code-block:: bash

		$ cd /path/to/typo3conf/ext/sphinx/Resources/Private/sphinx-sources/
		$ sudo rm -rf RestTools
		$ git clone git://git.typo3.org/Documentation/RestTools.git

The "build" button builds or rebuilds the corresponding version of the Sphinx environment with the TYPO3-related commands, PyYAML, Pygments, Python Imaging Library and ``rst2pdf``. **Good to know:** TypoScript support for Pygments is automatically updated, if needed, upon rebuilding your Sphinx environment.

Finally, the "remove" button removes both the sources and the corresponding version of the Sphinx environment.

.. important::
	This button *WILL NOT* remove sources of the TYPO3-related commands, the PyYAML library, Pygments, the Python Imaging
	Library or ``rst2pdf``.


Choosing the version of Sphinx
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

In the Extension Manager, configure this extension as usual:

|em_configure|

The first tab "basic" lets you choose which version of Sphinx should be used to render your documents:

|em-sphinx-version|


Choosing how to render PDF
^^^^^^^^^^^^^^^^^^^^^^^^^^

.. index::
	single: PDF; LaTeX
	single: PDF; rst2pdf

The second tab "pdf" lets you choose which PDF builder you prefer (either ``rst2pdf`` or LaTeX) and whether you want
to install and configure ``rst2pdf``:

|em-pdf|

.. tip::
	**Except for MS Windows users,** ``rst2pdf`` is available by default with this extension. However, if you want
	better output, you should consider using LaTeX instead. Please read chapter :ref:`admin-rendering-pdf` for instructions.


.. _install-plugins:

Choosing the 3rd-party plugins to install
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. index::
	pair: Plugins; Google Chart
	pair: Plugins; Google Maps
	pair: Plugins; HTTP Domain
	pair: Plugins; YouTube
	pair: Plugins; Slideshare

The third tab "3rd-party plugins" lets you activate additional Sphinx extensions. Some of them are available on
docs.typo3.org, and are as such suited for use with your extension manuals:

|em-plugins|

Please read chapter :ref:`third-party-plugins` for instructions on how to use them in your documents.

.. important::
	Make sure to :ref:`rebuild your Sphinx environment <configure-sphinx>` after activating new plugins.
