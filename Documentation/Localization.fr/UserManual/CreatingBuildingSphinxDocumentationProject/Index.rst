.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt
.. include:: Images.txt


.. _kickstart_sphinx_project:

Création et compilation d'un projet de documentation Sphinx
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. note::

	Cette section décrit la création et le rendu d'un projet de documentation Sphinx en utilisant le module Backend
	"Console Sphinx". Depuis la version 1.1.0, les développeurs d'extensions peuvent créer leur documentation basée sur
	Sphinx directement depuis l'écran d'accueil du module Backend :ref:`sphinx-documentation-viewer`. Ce module propose
	même un lien pour convertir un manuel OpenOffice existant en project Sphinx.

Utilisez le module Fichiers > Console Sphinx qui montre une arborescence de répertoires, choisissez un répertoire vide
et utilisez l'assistant qui est affiché automatiquement pour créer un nouveau projet de documentation Sphinx :

|

|project_wizard|

Un projet Sphinx vide sera créé. Vous pouvez ensuite modifier le fichier de configuration ``conf.py`` selon vos besoins.

Pour compiler un projet Sphinx, choisissez un répertoire contenant un projet de documentation et cliquez sur l'un des
boutons de compilation :

|

|project_properties|

La console affichera la sortie standard de la commande.


HTML
""""

Ce générateur Sphinx génère un site HTML autonome à partir de votre documentation dans le répertoire ``_build/html``.


JSON
""""

Ce générateur Sphinx génère un dérivé de HTML dans le répertoire ``_build/json``. Vous devriez utiliser ce générateur
afin de pouvoir intégrer proprement votre documentation à votre site TYPO3. L'extension
`reST Documentation Viewer (restdoc) <http://typo3.org/extensions/repository/view/restdoc>`_ peut être utilisée avec un
rendu JSON.


LaTeX
"""""

.. index::
	single: LaTeX

Ce générateur Sphinx génère un projet LaTeX à  partir de votre documentation dans le répertoire ``_build/latex``. Vous
avez besoin d'un environnement LaTeX pour compiler les projets LaTeX et générer de beaux documents PDF. Veuillez
vous référer au chapitre :ref:`rendering_pdf` pour plus d'informations.


PDF
"""

.. index::
	single: PDF

Si les commandes ``make`` et ``pdflatex`` sont disponibles sur votre serveur (ou si vous avez configuré ``rst2pdf``),
un bouton de compilation supplémentaire est affiché, pour vous permettre de générer une version PDF de votre
documentation automatiquement :

|build_button_pdf|


Vérification des liens
""""""""""""""""""""""

Ce générateur Sphinx vérifie tous les liens de votre documentation et prépare un rapport ``output.txt`` dans le répertoire
``_build/linkcheck/``.
