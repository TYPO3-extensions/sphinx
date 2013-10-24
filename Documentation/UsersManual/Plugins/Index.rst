.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _third-party-plugins:

3rd-party plugins
-----------------

.. index::
	single: Plugins; Use in document

This extension lets you :ref:`activate a few 3rd-party plugins <install-plugins>` (that is, Sphinx extensions
which are not an official part of Sphinx).

Each plugin typically comes with a dedicated documentation that you may read when installing them, within the
Extension Manager.

This chapter describes the process of using such a plugin within your documentation with a few examples of
plugins available as well on docs.typo3.org and as such suited for use within extension manuals.


Loading a plugin
^^^^^^^^^^^^^^^^

.. index::
	single: Plugins; Load in Settings.yml

In order to load a plugin, you need to add it to the list of extensions of configuration file :file:`conf.py`.
However, as this file does not exist when creating an extension manual, you should change configuration file
:file:`Settings.yml` instead as this file is used by docs.typo3.org (and this extension) to override default
parameters:

.. code-block:: yaml
	:linenos:

	conf.py:
	  copyright: 2013
	  project: Sphinx Python Documentation Generator and Viewer
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

Extension lines 7 to 10 should always be kept as this. Extension lines 11 to 14 are examples on how to load
3rd-party plugins for use in your document.


Examples
^^^^^^^^

.. only:: html

	.. contents::
		:local:
		:depth: 1


sphinxcontrib.googlechart
"""""""""""""""""""""""""

This is a Sphinx extension which render charts and graphs by using
`Google Chart <http://code.google.com/intl/ja/apis/chart/>`_ .

**Example:**

.. code-block:: rest

	.. piechart::
	    :size: 400x200

	    restdoc: 639
	    sphinx: 553
	    image_autoresize: 2261

**Rendered:**

.. only:: latex or missing_sphinxcontrib_googlechart

	.. image:: ../../Images/sphinxcontrib-googlechart.png
		:alt: sphinxcontrib.googlechart

.. only:: html

	.. piechart::
		:size: 400x200

		restdoc: 639
		sphinx: 553
		image_autoresize: 2261


sphinxcontrib.googlemaps
""""""""""""""""""""""""

This is a Sphinx extension which embeds maps using `Google Maps <http://maps.google.com/>`_.

**Example:**

.. code-block:: rest

	.. googlemaps::
	    :latitude: 46.804994
	    :longtitude: 7.153826

**Rendered:**

.. only:: latex or missing_sphinxcontrib_googlemaps

	.. image:: ../../Images/sphinxcontrib-googlemaps.png
		:alt: sphinxcontrib.googlemaps

.. only:: html

	.. googlemaps::
		:latitude: 46.804994
		:longtitude: 7.153826


sphinxcontrib.slide
"""""""""""""""""""

This is a Sphinx extension for embedding your presentation slides.

**Example:**

.. code-block:: rest

	.. slide:: http://www.slideshare.net/xperseguers/typo3-meets-xliff-rest

**Rendered:**

.. only:: latex or missing_sphinxcontrib_slide

	.. image:: ../../Images/sphinxcontrib-slide.png
		:alt: sphinxcontrib.slide

.. only:: html

	.. slide:: http://www.slideshare.net/xperseguers/typo3-meets-xliff-rest


sphinxcontrib.youtube
"""""""""""""""""""""

This is a Sphinx extension for embedding a YouTube video using its video ID.

**Example:**

.. code-block:: rest

	.. youtube:: YeGqHMDT7R8

**Rendered:**

.. only:: latex or missing_sphinxcontrib_youtube

	.. image:: ../../Images/sphinxcontrib-youtube.png
		:alt: sphinxcontrib.youtube

.. only:: html

	.. youtube:: YeGqHMDT7R8
