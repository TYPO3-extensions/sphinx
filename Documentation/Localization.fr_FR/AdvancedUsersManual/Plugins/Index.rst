.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt


.. _third-party-plugins:

Plugins externes
----------------

.. index::
	single: Plugins; Utilisation dans un document

Cette extension vous permet d':ref:`activer certains plugins externes <install-plugins>` (c.-à-d. des
extensions pour Sphinx qui ne font pas partie de la distribution officielle de base de Sphinx.

En général chaque plugin est fourni avec une courte documentation spécifique que vous pouvez lire lorsque vous
les installez, depuis le gestionnaire d'extensions.

Ce chapitre décrit les étapes nécessaires pour utiliser l'un des plugins dans votre documentation et montre
quelques exemples de tels plugins qui sont disponibles également sur docs.typo3.org et sont donc tout à fait
adaptés à une utilisation dans vos manuels d'extensions.


Chargement d'un plugin
^^^^^^^^^^^^^^^^^^^^^^

.. index::
	single: Plugins; Chargement avec Settings.yml

Afin de charger un plugin, vous devez l'ajouter à la liste des extensions du fichier de configuration :file:`conf.py`.
Cependant, comme ce fichier n'existe pas lorsque vous créez un manuel d'extension, vous devez modifier le fichier
de configuration :file:`Settings.yml` qui est utilisé par docs.typo3.org (et cette extension) pour surcharger les
paramètres par défaut :

.. code-block:: yaml
	:linenos:
	:emphasize-lines: 6-14

	conf.py:
	  copyright: 2013
	  project: Générateur et visionneuse de documentation Sphinx Python
	  version: 1.2
	  release: 1.2.0
	  extensions:
	  - sphinx.ext.intersphinx
	  - t3sphinx.ext.t3extras
	  - t3sphinx.ext.t3tablerows
	  - t3sphinx.ext.targets
	  - sphinxcontrib.googlechart
	  - sphinxcontrib.googlemaps
	  - sphinxcontrib.slide
	  - sphinxcontrib.youtube

Les lignes d'extensions 7 à 10 doivent être laissées telles quelles. Les lignes d'extensions 11 à 14 sont
des exemples de comment vous pouvez charger un plugin externe pour l'utiliser dans votre document.


Exemples
^^^^^^^^

.. only:: html

	.. contents::
		:local:
		:depth: 1


sphinxcontrib.googlechart
"""""""""""""""""""""""""

Extension Sphinx qui génère des graphiques et des graphes à l'aide de
`Google Chart <https://code.google.com/p/google-chartwrapper/>`__.

**Exemple :**

.. code-block:: rest

	.. piechart::
	    :size: 400x200

	    restdoc: 639
	    sphinx: 553
	    image_autoresize: 2261

**Rendered:**

.. only:: latex or missing_sphinxcontrib_googlechart

	.. image:: ../../../Images/sphinxcontrib-googlechart.png
		:alt: sphinxcontrib.googlechart

.. only:: html

	.. piechart::
		:size: 400x200

		restdoc: 639
		sphinx: 553
		image_autoresize: 2261


sphinxcontrib.googlemaps
""""""""""""""""""""""""

Extension Sphinx qui intègre des cartes `Google Maps <http://maps.google.com/>`__.

**Exemple :**

.. code-block:: rest

	.. googlemaps::
	    :latitude: 46.804994
	    :longtitude: 7.153826

**Rendu :**

.. only:: latex or missing_sphinxcontrib_googlemaps

	.. image:: ../../../Images/sphinxcontrib-googlemaps.png
		:alt: sphinxcontrib.googlemaps


.. only:: html

	.. googlemaps::
		:latitude: 46.804994
		:longtitude: 7.153826


sphinxcontrib.slide
"""""""""""""""""""

Extension Sphinx qui intègre des diapositives de présentation.

**Exemple :**

.. code-block:: rest

	.. slide:: http://www.slideshare.net/xperseguers/typo3-meets-xliff-rest

**Rendu :**

.. only:: latex or missing_sphinxcontrib_slide

	.. image:: ../../../Images/sphinxcontrib-slide.png
		:alt: sphinxcontrib.slide


.. only:: html

	.. slide:: http://www.slideshare.net/xperseguers/typo3-meets-xliff-rest


sphinxcontrib.youtube
"""""""""""""""""""""

Extension Sphinx qui intègre une vidéo YouTube en utilisant son ID.

**Exemple :**

.. code-block:: rest

	.. youtube:: YeGqHMDT7R8

**Rendu :**

.. only:: latex or missing_sphinxcontrib_youtube

	.. image:: ../../../Images/sphinxcontrib-youtube.png
		:alt: sphinxcontrib.youtube


.. only:: html

	.. youtube:: YeGqHMDT7R8
