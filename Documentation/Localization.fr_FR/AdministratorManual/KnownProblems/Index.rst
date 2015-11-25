.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt


.. _known-problems:

Problèmes connus
----------------

.. index::
	single: Message d'erreur; Python interpreter was not found
	single: Message d'erreur; Unzip cannot be executed
	single: Message d'erreur; ImportError: No module named setuptools
	single: Message d'erreur; Builder name pdf not registered

- Si malgré vos tentatives de corrections TYPO3 n'arrête pas de vous dire p. ex. "Python interpreter was not found" ou
  "Unzip cannot be executed", vous devriez vérifier votre configuration locale pour :php:`$TYPO3_CONF_VARS['SYS']['binPath']`
  et :php:`$TYPO3_CONF_VARS['SYS']['binSetup']`. Un utilisateur a pu corriger ce problème en modifiant les lignes en :

  .. code-block:: php

      $TYPO3_CONF_VARS['SYS']['binPath'] = '/usr/bin/';
      $TYPO3_CONF_VARS['SYS']['binSetup'] = 'python=/usr/bin/python,' .
                                            'unzip=/usr/bin/unzip,tar=/bin/tar';

  Une autre raison possible si vous voyez cette erreur après avoir manuellement configuré ``binSetup`` est que vous avez
  désactivé l'exécution des programmes dans le backend en définissant ``$TYPO3_CONF_VARS['BE']['disable_exec_function']``
  à ``1``.

  Un autre problème a été rapporté par une personne utilisant `Homebrew <http://brew.sh/>`_ avec Mac OS X. Bien que
  Python ait été correctement détecté et pouvait être utilisé, les binaires Sphinx n'étaient pas compilés. Ce problème
  a été corrigé en changeant l'ordre des chemins d'inclusion à utiliser avec ``$TYPO3_CONF_VARS['SYS']['binPath']`` et
  en s'assurant que la version de Python du système d'exploitation était utilisée en lieu et place de celle fournie par
  Homebrew.

- Certaines distributions de Linux (comme Fedora) ne fournissent pas ``docutils`` ou les fichiers d'en-tête (*header files*)
  et les bibliothèques pour développer des extensions Python. Depuis une Fedora en version standard, vous pouvez installer
  les composants manquants avec :

  .. code-block:: bash

      $ sudo yum install python python-docutils python-devel

- Typiquement sous Ubuntu Linux ou Mac OS X, la compilation de ``rst2pdf`` peut échouer avec ::

    Traceback (most recent call last):
      File "setup.py", line 7, in <module>
        from setuptools import setup, find_packages

  Auquel cas, vous devriez commencer par installer la bibliothèque Python ``setuptools``. Par exemple :

  .. code-block:: bash

      $ sudo apt-get install python-setuptools

- L'installation de Sphinx peut rapporter s'être effectuée avec succès alors qu'elle a en fait échoué lorsqu'une version
  ancienne de Python (< 2.4) est utilisée.

- Avec FAL (TYPO3 6.x) seul le stockage local (*LocalStorage*) a été implémenté et testé, ce qui signifie que le code
  devra être adapté pour pouvoir supporter d'autres types de stockages distants.

.. note::
	Veuillez svp utiliser le système de suivi de bogues de l'extension sur Forge pour rapporter de nouveaux bogues :
	https://forge.typo3.org/projects/extension-sphinx/issues
