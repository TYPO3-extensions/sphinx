.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt


.. _rendering_pdf:

Rendu PDF à partir de reStructuredText
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Sphinx utilise des *générateurs* pour préparer le rendu. Le "nom" du générateur doit être passé en ligne de commande
comme une option de ``sphinx-build`` avec ``-b``. Par exemple, pour un rendu HTML, cette extension invoque :

.. code-block:: bash

	$ sphinx-build -b html -c /chemin/vers/conf.py ...

Sphinx est capable de générer un PDF en utilisant soit LaTeX comme format intermédiaire ou ``rst2pdf``. Le rendu PDF
avec ``rst2pdf`` n'est de loin pas aussi bon que lorsque vous utilisez LaTeX mais il a le net avantage de ne pas
nécessiter d'installer un environnement LaTeX complet sur votre machine.

Le nom du générateur pour PDF en utilisant ``rst2pdf`` est simplement ``pdf`` :

.. code-block:: bash

	$ sphinx-build -b pdf -c /chemin/vers/conf.py ...

tandis que le nom du générateur pour un rendu LaTeX est ``latex`` :

.. code-block:: bash

	$ sphinx-build -b latex -c /chemin/vers/conf.py ...

Ce dernier génère un lot de fichiers LaTeX dans le répertoire de sortie. Vous devez spécifier quels documents doivent
être inclus avec la valeur de configuration `latex_documents <http://sphinx-doc.org/config.html#confval-latex_documents>`_.
Il y a quelques valeurs de configuration pour personnaliser le rendu de ce générateur. Consultez le chapitre
`Options for LaTeX output <http://sphinx-doc.org/config.html#latex-options>`_ pour plus de détails.

Une fois que les fichiers LaTeX ont été générés, le rendu PDF à proprement parler est réalisé par une simple compilation
des fichiers sources avec ``pdflatex`` :

.. code-block:: bash

	$ pdflatex nom-du-projet

Le reste de ce chapitre vous donne quelques informations supplémentaires sur LaTeX :

.. toctree::
	:maxdepth: 5
	:titlesonly:
	:glob:

	LaTeXVsRst2pdf
	IntroductionLaTeX
	CustomizingRendering
