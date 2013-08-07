.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt


Prérequis
^^^^^^^^^

.. index::
	pair: Répertoire; Structure

Cette extension a besoin d'un interpréteur Python disponible sur votre serveur web et -- bien entendu -- d'une
documentation écrite en reStructuredText sous forme d'un projet Sphinx.

.. note::
	Allez sur http://wiki.typo3.org/ReST pour plus d'informations sur reStructuredText.

.. tip::
	Cette extension installe et configure automatiquement ``rst2pdf`` pour le rendu PDF. Cependant, si vous désirez un
	meilleur rendu, vous devriez installer plutôt LaTeX. Veuillez lire le chapitre :ref:`rendering_pdf` pour les
	instructions.

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

et une structure de répertoire de documentation TYPO3 :

.. code-block:: none

	/chemin/vers/project/
	|-- ...
	`-- _make
	    |-- build
	    `-- conf.py
