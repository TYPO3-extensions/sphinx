.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _known-problems:

Problèmes connus
================

.. index::
	single: Message d'erreur; ImportError: No module named setuptools
	single: Message d'erreur; Builder name pdf not registered

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

- La génération d'un PDF peut échouer avec "Builder name pdf not registered" lorsque vous utilisez ``rst2pdf``. Cela
  est provoqué par l'impossibilité de modifier le fichier de configuration global
  ``Resources/Private/sphinx-sources/RestTools/`` ``ExtendingSphinxForTYPO3/src/t3sphinx/settings/GlobalSettings.yml``
  par le serveur web. Ce fichier est modifié pour autoriser ``rst2pdf`` à être utilisé lors de mise en place de
  l'environnement Sphinx depuis le gestionnaire d'extensions. Si ce fichier ne peut pas être modifié par l'utilisateur
  utilisé par serveur web, vous pouvez le patcher manuellement en ajoutant une référence à l'extension
  ``rst2pdf.pdfbuilder`` (ligne 9) :

  .. code-block:: yaml
      :linenos:

      extensions:
        - sphinx.ext.extlinks
        - sphinx.ext.ifconfig
        - sphinx.ext.intersphinx
        - sphinx.ext.todo
        - t3sphinx.ext.t3extras
        - t3sphinx.ext.t3tablerows
        - t3sphinx.ext.targets
        - rst2pdf.pdfbuilder

- Avec FAL (TYPO3 6.x) seul le stockage local (*LocalStorage*) a été implémenté et testé, ce qui signifie que le code
  devra être adapté pour pouvoir supporter d'autres types de stockages distants.

.. note::
	Veuillez svp utiliser le système de suivi de bogues de l'extension sur Forge pour rapporter de nouveaux bogues :
	https://forge.typo3.org/projects/extension-sphinx/issues
