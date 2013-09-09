.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt
.. include:: Images.txt


.. _introduction:

Présentation
============


.. _what-it-does:

Qu'est-ce que ça fait ?
-----------------------

.. only:: latex

	Cette extension vous permet de générer des projets de documentation écrits avec Sphinx_ (the générateur de
	documentation Python que l'équipe de documentation de TYPO3 a choisi pour tous les manuels officiels) depuis le
	Backend de TYPO3. Regarder le `tutoriel vidéo de 5 min`_ (anglais).

.. only:: html

	Cette extension vous permet de générer des projets de documentation écrits avec Sphinx_ (the générateur de
	documentation Python que l'équipe de documentation de TYPO3 a choisi pour tous les manuels officiels) depuis le
	Backend de TYPO3 :

	.. youtube:: YeGqHMDT7R8
		:width: 100%

	|

Sphinx a été créé à l'origine pour préparer la documentation Python et certaines caractéristiques valent la peine d'être
mises en évidence :

- **Formats de sortie :** HTML, JSON (un dérivé de HTML utilisé par l'extension TYPO3 "`restdoc <http://typo3.org/extensions/repository/view/restdoc>`_"), LaTeX (pour une version PDF imprimable), texte simple, ...

- **Références croisées :** balisage sémantique et liens automatiques pour les citations, glossaire de termes et éléments
  d'information similaires. Par exemple, la documentation officielle de TYPO3 offre des ressources pour référencer
  n'importe quel chapitre ou section d'une documentation officielle TYPO3 depuis vos propres documents. Pour plus
  d'informations, veuillez consulter la page `Trucs et astuces <http://wiki.typo3.org/Tips_and_Tricks_%28reST%29>`_
  (anglais) dans le wiki de TYPO3

- **Structure hiérarchie :** définition simple d'une arborescence de documentation, avec liens automatiques vers les
  documents frères, parents ou fils

- **Index automatique :** index général des termes utilisés dans votre documentation

- **Extensions :** l'outil vous permet de l'étendre avec vos propres modules

.. Liens :
.. _`tutoriel vidéo de 5 min`: http://www.youtube.com/watch?v=YeGqHMDT7R8


Et cette extension ?
^^^^^^^^^^^^^^^^^^^^

La mise en place d'un environnement Sphinx pour générer de la documentation peut être compliqué aux yeux de certains
utilisateurs. Cette extension part du principe qu'un interpréteur Python est disponible sur votre serveur web et se
charge d'installer et de configurer Sphinx **localement** (c.-à-d. dans votre site) en quelques clics.

Par ailleurs, cette extension fournit quelques utilitaires comme :

- Une visionneuse de documentation en Backend
- Un module de création rapide d'un project de documentation Sphinx
- Un module pour compiler un projet Sphinx
- Un éditeur reStructuredText intégré
- Un assistant pour convertir un document OpenOffice (``manual.sxw``) en un projet Sphinx (à l'aide d'un utilitaire en
  ligne disponible sur http://docs.typo3.org)


Que puis-je faire avec un project Sphinx ?
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Plein de choses ! Et avant tout, si vous compilez votre documentation en JSON, vous pouvez facilement l'intégrer à votre
site. Le meilleur moyen est d'utiliser l'extension TYPO3 `reST Documentation Viewer (restdoc)`_.


.. _screenshots:

Copies d'écran
--------------

|project_wizard_overview|

|

Générer un project de documentation Sphinx existant
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

|mod1_overview|

|

Générer un PDF avec pdflatex
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

|build_buttons|

|

Générer et parcourir les manuels d'autres extensions
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

|viewer|
