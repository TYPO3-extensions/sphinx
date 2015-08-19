.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _developer-manual:

Manuel du développeur
=====================

.. only:: html

	Ce chapitre décrit certains mécanismes internes de l'extension Sphinx pour vous permettre de l'étendre facilement.


Signaux et slots
----------------

Le concept de *signaux* et *slots* permet une implémentation facile du patron de conception
`observateur/observable <https://fr.wikipedia.org/wiki/Observateur_%28patron_de_conception%29>`__ en génie logiciel, un
concept similaire aux *hooks* dans les versions précédentes de TYPO3. Son implémentation dans le CMS TYPO3 a été
rétroporté de Flow; veuillez donc lire le chapitre
`Signals and Slots <https://flowframework.readthedocs.org/en/stable/TheDefinitiveGuide/PartIII/SignalsAndSlots.html>`__
de la documentation officielle Flow. En résumé, des *signaux* sont placés dans le code et envoyés lors de l'exécution
aux *slots* qui se sont enregistrés.

Signaux et slots disponibles :

.. toctree::
	:maxdepth: 2

	SignalSlots/RegisteringCustomDocumentation
	SignalSlots/PostProcessingLaTeX
