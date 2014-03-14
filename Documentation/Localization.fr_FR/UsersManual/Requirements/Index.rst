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

.. note::
	Vous ne savez pas comment démarrer avec reStructuredText et Sphinx ? Vous pouvez peut-être jeter un œil au
	document `reStructuredText Primer for TYPO3 Users <https://github.com/xperseguers/TYPO3.docs.rst-primer>`_. C'est
	une brève introduction aux concepts et à la syntaxe reStructuredText (reST), qui a pour but de permettre aux
	rédacteurs d'avoir suffisamment d'information pour rédiger des documents de façon efficace.

	Vous pouvez également vous rendre sur http://wiki.typo3.org/ReST pour plus d'informations sur reStructuredText.

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
