.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _known-problems:

Known problems
--------------

.. index::
	single: Error Message; ImportError: No module named setuptools
	single: Error Message; Builder name pdf not registered

- A few Linux distributions (such as Fedora) do not provide ``docutils`` or the header files and libraries to develop Python
  extensions. With a vanilla Fedora, you may install missing components with:

  .. code-block:: bash

      $ sudo yum install python python-docutils python-devel

- Typically on Ubuntu Linux or Mac OS X, build of ``rst2pdf`` may fail with::

    Traceback (most recent call last):
      File "setup.py", line 7, in <module>
        from setuptools import setup, find_packages

  If so, you should first install the Python library ``setuptools``. E.g.,

  .. code-block:: bash

      $ sudo apt-get install python-setuptools

- Sphinx installation may report as having completed successfully although it actually failed when an old version of Python is used (< 2.4).

- The rendering of a PDF may fail with "Builder name pdf not registered" when using ``rst2pdf``. This is caused by global configuration
  file ``Resources/Private/sphinx-sources/RestTools/ExtendingSphinxForTYPO3/src/t3sphinx/settings/GlobalSettings.yml`` not being writable
  by the web server. This file is modified to support ``rst2pdf`` while building the Sphinx environment in Extension Manager.

- With FAL (TYPO3 6.x) only LocalStorage has been implemented and tested, meaning code will need to be adapted in order to deal with other types of remote storage.

.. note::
	Please use the extension's bug tracker on Forge to report bugs: https://forge.typo3.org/projects/extension-sphinx/issues
