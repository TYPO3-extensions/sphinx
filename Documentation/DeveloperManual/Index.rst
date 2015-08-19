.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _developer-manual:

Developer manual
================

.. only:: html

	This chapter describes some internals of the Sphinx extension to let you extend it easily.


Signals and Slots
-----------------

The concept of *signals* and *slots* allows for easy implementation of the
`Observer pattern <https://en.wikipedia.org/wiki/Observer_pattern>`__ in software, something similar to *hooks* in former
versions of TYPO3. Its implementation in TYPO3 CMS has been backported from Flow, so please read chapter
`Signals and Slots <https://flowframework.readthedocs.org/en/stable/TheDefinitiveGuide/PartIII/SignalsAndSlots.html>`__
from Flow official documentation. In short, *signals* are put into the code and call registered *slots* when run through.

Available signals and slots:

.. toctree::
	:maxdepth: 2

	SignalSlots/RegisteringCustomDocumentation
	SignalSlots/PostProcessingLaTeX
