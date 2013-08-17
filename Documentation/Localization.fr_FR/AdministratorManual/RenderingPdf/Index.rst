.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt


.. _admin-rendering-pdf:

Rendu PDF depuis reStructuredText
---------------------------------

Sphinx utilise des *générateurs* pour préparer le rendu. Le "nom" du générateur pour un rendu PDF est soit ``latex``
(meilleur rendu) soit ``rst2pdf``.

``rst2pdf`` est un utilitaire écrit en Python et disponible sur http://rst2pdf.ralsina.com.ar/. Cet utilitaire est
automatiquement installé et configuré lorsque vous installez cette extension. Le rendu PDF avec ``rst2pdf`` n'est de loin
pas aussi bon que lorsque vous utilisez LaTeX mais il a le net avantage de ne pas nécessiter d'installer un environnement
LaTeX complet sur votre machine.

.. warning::
	**Utilisateurs MS Windows :** L'installation automatique de ``rst2pdf`` n'est malheureusement pas possible pour le
	moment car elle nécessite des composants supplémentaires tels qu'un compilateur GCC. Veuillez consulter
	http://forge.typo3.org/issues/49530 pour plus d'information.

Le reste de ce chapitre vous guide au travers de l'installation et de la configuration de LaTeX :

.. toctree::
	:maxdepth: 5
	:titlesonly:
	:glob:

	InstallingLaTeXLinux
	InstallingLaTeXWindows
	InstallingShareFont
