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
`observateur/observable <http://fr.wikipedia.org/wiki/Observateur_%28patron_de_conception%29>`_ en génie logiciel, un
concept similaire aux *hooks* dans les versions précédentes de TYPO3. Son implémentation dans le CMS TYPO3 a été
rétroporté de Flow; veuillez donc lire le chapitre `Signals and Slots <http://docs.typo3.org/flow/TYPO3FlowDocumentation/TheDefinitiveGuide/PartIII/SignalsAndSlots.html>`_ de la documentation officielle Flow.
En résumé, des *signaux* sont placés dans le code et envoyés lors de l'exécution aux *slots* qui se sont enregistrés.

Signaux et slots disponibles :

.. toctree::
	:maxdepth: 2

	SignalSlots/RegisteringCustomDocumentation
