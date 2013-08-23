.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt


.. _advanced-cross-links:

Références croisées avancées
----------------------------

.. index::
	single: Intersphinx

.. admonition:: Avertissement
	:class: warning

	Cette section aborde un sujet difficile que vous pouvez ignorer à moins de vouloir faire des références croisées
	vers (virtuellement) n'importe quel site.

Cette section commence par décrire le format du fichier d'index ``objects.inv`` qui est utilisé par Intersphinx_ et
détaille ensuite comment générer automatiquement un tel fichier pour une documentation d'API basée sur Doxygen_.

.. _Intersphinx: http://sphinx-doc.org/ext/intersphinx.html

.. _Doxygen: www.doxygen.org/


Format de ``objects.inv``
^^^^^^^^^^^^^^^^^^^^^^^^^

.. index::
	single: objects.inv (format)

Un fichier d'index ``objects.inv`` consiste en deux parties ; la première est du texte pur, au début, suivie d'une liste
de références compressée avec ZLIB :

.. code-block:: bash

	# Sphinx inventory version 2
	# Project: Mon projet
	# Version: 1.0.0
	# The remainder of this file is compressed using zlib.
	... contenu compressé avec zlib ...

La première ligne est vérifiée par Intersphinx pour s'assurer que le fichier a le bon format. Nous ne décrivons que la
version 2 de ce format d'index.

La liste de références compressée avec ZLIB a la structure suivante :

.. code-block:: none

	<nom ancre #1> std:label -1 <url cible #1> <titre de l'ancre #1>
	<nom ancre #2> std:label -1 <url cible #2> <titre de l'ancre #2>
	<nom ancre #3> std:label -1 <url cible #3> <titre de l'ancre #3>

<nom ancre>
	Nom de l'ancre en minuscules. Vous pouvez utiliser les caractères spéciaux "-" (tiret), "_" (trait de soulignement)
	et "\\" (anti-asYou may use special characters such as "-" (dash), "_" (underscore) and "\\"
	(barre oblique inversée) pour séparer les mots.

<url cible>
	URL relative vers la page qui se termine par :

	- ``#``: pour pointer vers le début de la page (p. ex. ``admonitions.html#``) *ou*
	- ``#$``: pour pointer vers la page et ajouter ``<nom ancre>`` (comme demandé par ``$``) *ou*
	- ``#une-ancre``: pour pointer vers une ancre arbitraire de la page (p. ex. ``admonitions.html#0145384da``).

<titre de l'ancre>
	Titre par défaut à utiliser lorsqu'une référence croisée ne contient pas de titre de remplacement :

	.. code-block:: restructuredtext

		Titre automatique : :ref:`préfixe:mon-ancre`
		Titre de remplacement : :ref:`Mon titre de remplacement <préfixe:mon-ancre>`


Documentation Doxygen
^^^^^^^^^^^^^^^^^^^^^

Qu'est-ce que Doxygen ?

	Doxygen is the de facto standard tool for generating documentation from annotated C++ sources, but it also supports
	other popular programming languages such as C, Objective-C, C#, PHP, Java, Python, IDL (Corba, Microsoft, and
	UNO/OpenOffice flavors), Fortran, VHDL, Tcl, and to some extent D.

	-- Dimitri van Heesch


Le but de référencer Doxygen est d'être en mesure d'écrire quelque chose comme suit dans un document pour développeurs :

.. code-block:: restructuredtext

	Veuillez regarder la classe :ref:`t3cmsapi:TYPO3\\CMS\\Core\\Utility\\GeneralUtility`
	pour les détails.

	Lorsque vous instanciez une classe depuis une extension TYPO3, vous ne devriez pas
	utiliser le mot-clé ``new`` mais faire un appel
	à :ref:`t3cmsapi:TYPO3\\CMS\\Core\\Utility\\GeneralUtility::makeInstance`.

Le résultat escompté est  :

	Veuillez regarder la classe `\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility`_ pour les détails.

	Lorsque vous instanciez une classe depuis une extension TYPO3, vous ne devriez pas utiliser le mot-clé
	``new`` mais faire un appel à `\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility::makeInstance()`_.


.. _`\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility`: http://typo3.org/api/typo3cms/class_t_y_p_o3_1_1_c_m_s_1_1_core_1_1_utility_1_1_general_utility.html
.. _`\\TYPO3\\CMS\\Core\\Utility\\GeneralUtility::makeInstance()`: http://typo3.org/api/typo3cms/class_t_y_p_o3_1_1_c_m_s_1_1_core_1_1_utility_1_1_general_utility.html#a99623a1a2f1f8369d19d0e58c7feb4b0

Le problème de Doxygen est qu'il génère des noms de fichiers cryptiques qui sont très difficiles à référencer
manuellement. Fort heureusement, nous avons trouvé un moyen de générer un fichier d'index ``objects.inv`` en parcourant
la sortie XML d'une documentation d'API.

Vous avez besoin des versions HTML et XML de la documentation. Pour générer la version XML, il vous suffit de rajouter ::

	GENERATE_XML   =   YES

à votre fichier de configuration Doxygen. Générez ensuite votre documentation d'API comme d'habitude. Vous devriez vous
retrouver avec deux répertoires ``html`` et ``xml`` (en gardant les options de configuration par défaut).

Exécutez ensuite le script suivant :

.. literalinclude:: ../../../UsersManual/AdvancedCrossLinks/prepare-objects-inv.sh
	:language: bash

.. warning::
	Le script a des dépendances vers deux programmes :

	* xmlstarlet_
	* PHP CLI

.. _xmlstarlet: http://xmlstar.sourceforge.net/

Le fichier d'index ``objects.inv`` sera stocké avec votre documentation HTML. Vous pouvez ensuite déployer ce répertoire
HTML sur votre site et :ref:`faire des références croisées comme d'habitude <docs-typo3-org-crosslink>`.
