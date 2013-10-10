.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt
.. include:: Images.txt


Installation de LaTeX sous MS Windows
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Téléchargez et exécutez l'installateur MiKTeX_ pour mettre en place un système TeX/LaTeX de base sur votre ordinateur.
Vous pouvez lire le chapitre `Installing MiKTeX`_ dans le manuel MiKTeX si vous êtes intéressé à connaître tous les détails.

.. _MiKTeX: http://www.miktex.org/download

.. _`Installing MiKTeX`: http://docs.miktex.org/2.9/manual/installing.html

.. note::

	Le paquet qu'il est recommandé de télécharger est l'installateur MiKTeX Basic et signifie que le programme va
	installer les composants manquants à la volée, en se connectant en ligne.

	Sinon, vous pouvez choisir d'installer une version complète de MiKTeX (*MiKTeX Net Installer* sous "Other Downloads"
	sur le site MiKTeX). Mais gardez à l'esprit qu'il en résulte une empreinte d'utilisation du disque *beaucoup plus
	importante*.

|miktex_setup|

.. important::
	L'option "Install MiKTeX for anyone who uses this computer" est nécessaire si votre serveur web s'exécute avec un
	utilisateur dédié.

À l'étape 3, l'installateur va vous demander si vous souhaitez installer les paquets manquants à la volée. Nous recommandons
**vivement** de laisser MiKTeX installer ces composants supplémentaires sans interaction avec vous. Le raisonnement est
que ça améliore le confort d'utilisation lorsque vous générer des documents depuis votre site TYPO3 parce que l'interaction
avec le compilateur n'est pas disponible sans ligne de commande et va résulter en une erreur de compilation si un paquet
LaTeX n'est pas disponible sur votre système :

|miktex_onthefly|

.. tip::

	Lorsque vous avez installé MiKTeX, il est recommandé d'exécuter l'assistant de mise à jour pour récupérer les
	dernières corrections.

Après que l'installation s'est terminée avec succès, votre variable d'environnement ``%PATH%`` doit être mise à jour
pour rendre les commandes LaTeX disponibles globalement. Ensuite, il est nécessaire de simplement redémarrer Apache afin
que TYPO3 détecte lesdites commandes étant donné qu'Apache ne charge le contenu de ``%PATH%`` qu'une seule fois, au
démarrage.
