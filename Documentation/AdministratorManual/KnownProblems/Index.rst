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

- With FAL (TYPO3 6.x) only LocalStorage has been implemented and tested, meaning code will need to be adapted in order to deal with other types of remote storage.

.. note::
	Please use the extension's bug tracker on Forge to report bugs: https://forge.typo3.org/projects/extension-sphinx/issues
