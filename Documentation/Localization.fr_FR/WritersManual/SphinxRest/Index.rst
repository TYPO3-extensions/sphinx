.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt


.. _sphinx-rest:

Sphinx et reStructuredText
--------------------------

.. index::
	pair: reStructuredText; Syntaxe

Quelques liens pour bien démarrer avec Sphinx et reStructuredText :

- `reStructuredText Syntax <http://wiki.typo3.org/ReST_Syntax>`_


Conversion d'un manuel OpenOffice en Sphinx
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Le dépot RestTools (http://git.typo3.org/Documentation/RestTools.git) fournit un script dans le répertoire
:file:`T3PythonDocBuilderPackage/src/T3PythonDocBuilder` pour convertir votre manuel OpenOffice en Sphinx/reStructuredText.
Veuillez lire le fichier :file:`README` correspondant pour plus d'informations.

Si vous préférez, vous pouvez utiliser un convertisseur en ligne : http://docs.typo3.org/getthedocs/service-convert.html.


Trucs et astuces pour le rendu PDF
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. index::
	single: Image; Formats supportés

Le rendu PDF sur http://docs.typo3.org utilise LaTeX comme décrit dans le chapitre :ref:`rendering-pdf`. C'est pourquoi
vous devez vous limiter à utiliser des images en JPG (image/jpeg) ou PNG (image/png) et ne pas avoir d'images au format
GIF (image/gif) qui empêche le rendu PDF.
