.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt
.. include:: Images.txt


.. _sphinx-documentation-editor:

Éditeur de documentation Sphinx
-------------------------------

Lorsque vous affichez un manuel d'extension en utilisant le :ref:`gabarit interactif <layouts>`, la barre d'outils
standard de TYPO3 affiche une icône de crayon pour vous permettre de modifier le chapitre correspondant :

|

|edit_chapter|


Modification d'un document
^^^^^^^^^^^^^^^^^^^^^^^^^^

L'icône de crayon charge l'éditeur "Ace" (http://ace.ajax.org/).

Cet éditeur vous permet de mettre à jour rapidement le chapitre correspondant et de générer à nouveau la documentation
lorsque vous cliquez sur l'icône "enregistrer et fermer" :

|

|save_compile|

.. note::
	L'éditeur Ace ne propose pas pour l'instant de coloration syntaxique pour reStructuredText et est par conséquent
	configuré pour utiliser Markdown à la place.

Sur le côté droit, un panneau vous montre un navigateur de références au sein de votre documentation. Les
références sont groupées par chapitre sous la forme d'un accordéon :

|reference-browser|

Tout en haut, vous trouvez une zone de texte qui vous permet d'afficher les références de toutes les extensions
et les manuels officiels dotés d'une documentation basée sur reStructuredText/Sphinx. Il vous suffit de taper
une clé d'extension, une partie du titre d'une extension ou quelques mots issus de sa description et de
sélectionner l'entrée correspondante grâce au mécanisme de complètement automatique.

.. only:: latex or missing_sphinxcontrib_youtube

	Une fois que vous avez trouvé la référence qui vous intéresse, il vous suffit de cliquer sur celle-ci pour
	l'insérer avec la syntaxe reStructuredText correcte dans votre document.

.. only:: html

	Une fois que vous avez trouvé la référence qui vous intéresse, il vous suffit de cliquer sur celle-ci pour
	l'insérer avec la syntaxe reStructuredText correcte dans votre document :

	.. youtube:: TShEf6YkREA
		:width: 100%

	|

Si la référence que vous insérez n'est pas issue de votre documentation (c.-à-d. que vous référencez un autre
chapitre ou section) mais est une référence croisée vers un autre document, la partie Intersphinx de votre
fichier de configuration ``Settings.yml`` sera automatiquement mise à jour. Au besoin, veuillez lire la
section :ref:`docs-typo3-org-crosslink` pour plus d'informations.
