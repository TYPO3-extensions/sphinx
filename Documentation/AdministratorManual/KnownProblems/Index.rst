.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _known-problems:

Known problems
--------------

.. index::
	single: Error Message; Python interpreter was not found
	single: Error Message; Unzip cannot be executed
	single: Error Message; ImportError: No module named setuptools
	single: Error Message; Builder name pdf not registered
	single: Error Message; LaTeX Error: File 'typo3.sty' not found

- If TYPO3 keeps failing with e.g., "Python interpreter was not found" or "Unzip cannot be executed", you should
  check your local configuration for :php:`$TYPO3_CONF_VARS['SYS']['binPath']` and :php:`$TYPO3_CONF_VARS['SYS']['binSetup']`.
  A user reported having fixed this problem by changing lines to:

  .. code-block:: php

      $TYPO3_CONF_VARS['SYS']['binPath'] = '/usr/bin/';
      $TYPO3_CONF_VARS['SYS']['binSetup'] = 'python=/usr/bin/python,' .
                                            'unzip=/usr/bin/unzip,tar=/bin/tar';

  Another reason for seeing this error, even when ``binSetup`` is manually configured is that you accidentally disabled
  execution of programs in the backend by setting ``$TYPO3_CONF_VARS['BE']['disable_exec_function']`` to ``1``.

  Yet another related problem has been reported by someone using `Homebrew <http://brew.sh/>`_ with Mac OS X. Although
  Python was properly detected and could be used, the Sphinx binaries were not built correctly. This was fixed by
  changing the order of paths to be searched for in ``$TYPO3_CONF_VARS['SYS']['binPath']`` to ensure the system version
  of Python being used instead of the one coming with Homebrew.

- A few Linux distributions (such as Fedora) do not provide ``docutils`` or the header files and libraries to
  develop Python extensions. With a vanilla Fedora, you may install missing components with:

  .. code-block:: bash

      $ sudo yum install python python-docutils python-devel

- Typically on Ubuntu Linux or Mac OS X, build of :program:`rst2pdf` may fail with::

    Traceback (most recent call last):
      File "setup.py", line 7, in <module>
        from setuptools import setup, find_packages

  If so, you should first install the Python library ``setuptools``. E.g.,

  .. code-block:: bash

      $ sudo apt-get install python-setuptools

- Sphinx installation may report as having completed successfully although it actually failed when an old version of Python is used (< 2.4).

- The rendering of a PDF may fail with "Builder name pdf not registered" when using :program:`rst2pdf`. This is caused
  by global configuration file :file:`uploads/tx_sphinx/RestTools/` :file:`ExtendingSphinxForTYPO3/src/t3sphinx/settings/GlobalSettings.yml`
  not being writable by the web server. This file is modified to support :program:`rst2pdf` while building the Sphinx
  environment in Extension Manager. If this file cannot be written by the web server user, you may patch it manually by
  adding a reference to extension ``rst2pdf.pdfbuilder`` (line 9):

  .. code-block:: yaml
      :linenos:
      :emphasize-lines: 9

      extensions:
        - sphinx.ext.extlinks
        - sphinx.ext.ifconfig
        - sphinx.ext.intersphinx
        - sphinx.ext.todo
        - t3sphinx.ext.t3extras
        - t3sphinx.ext.t3tablerows
        - t3sphinx.ext.targets
        - rst2pdf.pdfbuilder

- When using LaTeX to build PDF, the rendering may fail with:

  .. code-block:: bash

       LaTeX Error: File `typo3.sty' not found.

  This happens if your documentation is trying to produce a PDF with TYPO3 branding but without having followed the
  instructions from chapter :ref:`installing-share-font`. What is actually needed is the clone of the RestTools Git
  repository, not the Share font per se.

  This problem may pop up as well if you try to render your manual as PDF from the command line without having installed
  ``typo3.sty`` as a global LaTeX package.

- With FAL (TYPO3 6.x) only LocalStorage has been implemented and tested, meaning code will need to be adapted in order to deal with other types of remote storage.

.. note::
	Please use the extension's bug tracker on Forge to report bugs: https://forge.typo3.org/projects/extension-sphinx/issues
