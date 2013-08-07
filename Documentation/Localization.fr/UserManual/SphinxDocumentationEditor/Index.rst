.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt
.. include:: Images.txt


.. _sphinx-documentation-editor:

Éditeur de documentation Sphinx
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Lorsque vous affichez un manuel d'extension en utilisant le :ref:`gabarit interactif <layouts>`, la barre d'outils
standard de TYPO3 affiche une icône de crayon pour vous permettre de modifier le chapitre correspondant :

|

|edit_chapter|


Modification d'un document
""""""""""""""""""""""""""

L'icône de crayon charge une version en ligne de l'éditeur "Ace" (http://ace.ajax.org/). Par conséquent ce module
nécessite une connexion à Internet et il n'est pas actuellement possible de modifier les fichiers en étant déconnecté.

Cet éditeur vous permet de mettre à jour rapidement le chapitre correspondant et de générer à nouveau la documentation
lorsque vous cliquez sur l'icône "enregistrer et fermer" :

|

|save_compile|

.. note::
	L'éditeur Ace ne propose pas pour l'instant de coloration syntaxique pour reStructuredText et est par conséquent
	configuré pour utiliser Markdown à la place.
