.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt
.. include:: Images.txt


Installing the extension
^^^^^^^^^^^^^^^^^^^^^^^^

There are a few steps necessary to install the Sphinx Python Documentation Generator extension. If you have installed other extensions in the past, you will run into little new here.


1) Install the extension from Extension Manager
"""""""""""""""""""""""""""""""""""""""""""""""

The Sphinx Python Documentation Generator extension can be installed through the typical TYPO3 installation process using the Extension Manager.


2) Download and configure Sphinx
""""""""""""""""""""""""""""""""

In the Extension Manager, execute the update script this extension is providing:

|em_update|

Select a version of Sphinx you would like to use and start the import process with the "import" button:

|import_sphinx|

.. index::
	single: Install; Log
	single: Install; PyYAML
	single: Install; TYPO3 ReST Tools

Everything should work out-of-the-box. Possible problems will be reported as Flash messages and a log of all operations is stored as ``typo3temp/tx_sphinx/IMPORT-<date>.log``. The general process of importing Sphinx is as follows:

1. Fetch the version as a zip archive from https://bitbucket.org/birkenfeld/sphinx/downloads ("Tags") into ``typo3temp/``

2. Unpack the zip archive into ``EXT:sphinx/Resources/Private/sphinx-sources/<version>/``

3. Build the Python libraries into ``EXT:sphinx/Resources/Private/sphinx/<version>/``

4. Install TYPO3-related commands provided by the TYPO3 Documentation Team (TYPO3 ReST tools)

5. Install PyYAML library (http://pyyaml.org/wiki/PyYAML), needed for building TYPO3 documentation

The manual process buttons let you locally change files and rebuild your environment. This is particularly useful if you want to use the `git repository of the TYPO3 ReST tools`_ instead of a snapshot.

The "download" button fetches the corresponding sources of Sphinx, the TYPO3-related commands and the PyYAML library if they are not available locally.

The "build" button builds or rebuilds the corresponding version of the Sphinx environment with the TYPO3-related commands and PyYAML.

Finally, the "remove" button removes both the sources and the corresponding version of the Sphinx environment.

.. important::
	This button *WILL NOT* remove sources of the TYPO3-related commands and the PyYAML library.


3) Choose the version of Sphinx to use
""""""""""""""""""""""""""""""""""""""

In the Extension Manager, configure this extension as usual:

|em_configure|

And then choose which version of Sphinx should be used:

|sphinx_version|
