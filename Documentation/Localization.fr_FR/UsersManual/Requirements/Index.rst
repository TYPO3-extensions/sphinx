.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt


Prérequis
---------

.. index::
	pair: Répertoire; Structure

Cette extension a besoin d'un interpréteur Python disponible sur votre serveur web et -- bien entendu -- d'une
documentation écrite en reStructuredText sous forme d'un projet Sphinx.

.. tip::
	Cette extension permet d'installer et de configurer automatiquement :program:`rst2pdf` pour le rendu PDF. Cependant,
	si vous désirez un meilleur rendu, vous devriez installer plutôt LaTeX. Veuillez lire le chapitre :ref:`rendering-pdf`
	pour les instructions.

Cette extension supporte des projets dans un répertoire unique :

.. code-block:: none

	/chemin/vers/project/
	|-- _build
	|-- conf.py
	`-- ...

des projets avec répertoires source/build séparés :

.. code-block:: none

	/chemin/vers/project/
	|-- build
	`-- source
	    |-- conf.py
	    `-- ...

et une structure de répertoires de documentation TYPO3 :

.. code-block:: none

	/chemin/vers/project/
	|-- ...
	`-- _make
	    |-- build
	    `-- conf.py

Le :ref:`tableau de bord <dashboard>` vous permet de rapidement créer de nouveaux projets de documentation avec la
structure qui vous convient le mieux.
