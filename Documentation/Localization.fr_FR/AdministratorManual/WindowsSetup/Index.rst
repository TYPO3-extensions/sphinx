﻿.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt


.. _windows-setup:

Mise en place de l'environnement avec MS Windows
------------------------------------------------

À la différence de la plupart des systèmes d'exploitation :abbr:`UN*X (Unix-like)`, MS Windows ne fournit pas de façon
systématique un interpréteur Python and certains utilitaires généraux.

Comme cette extension a besoin de Python et des utilitaires d'extraction d'archives unzip et tar, les utilisateurs
MS Windows doivent tout d'abord configurer leur environnement avec le framework Python. Depuis un Windows de base,
lorsque vous exécutez le script de mise à niveau de cette extension dans le gestionnaire d'extensions en cliquant sur
l'icône d'action |download_python| (|update_script| avant TYPO3 6.2) vous verrez quelques messages d'erreurs :

.. |update_script| image:: ../../../Images/update_script.png
	:alt: Exécution du script de mise à niveau dans le gestionnaire d'extensions

.. |download_python| image:: ../../../Images/download.png
	:alt: Téléchargement de Sphinx

.. index::
	single: Message d'erreur; Python interpreter was not found
	single: Message d'erreur; Unzip cannot be executed
	single: Message d'erreur; Tar cannot be executed

.. figure:: ../../../Images/environment_check_windows.png
	:alt: Vérification de l'environnement sous MS Windows

Les sections suivantes décrivent comment installer :

- :ref:`Python <installing-python>`
- :ref:`Unzip <installing-unzip>`
- :ref:`Tar <installing-tar>`

.. note::
	Ces instructions ont été testées avec :

	- Microsoft Windows 7 Professional, 64 bit edition
	- `WampServer (64 bit & PHP 5.4) 2.2E <http://www.wampserver.com/#download-wrapper>`_
	- Python 2.x

.. tip::
	.. index::
		single: Message d'erreur; MSVCR100.dll is missing from your computer

	L'installation de WampServer peut planter avec un message d'erreur similaire à :

	.. image:: ../../../Images/msvcr100dll.png
		:alt: Redistribuable Visual C++ pour manquant

	Ce message d'erreur un peu cryptique signifie que vous devez installer les redistribuables de Microsoft Visual C++
	qui peuvent être téléchargées depuis le site Microsoft en version x86 ou x64 :

	- **32 bit:** http://www.microsoft.com/fr-fr/download/details.aspx?id=5555
	- **64 bit:** http://www.microsoft.com/fr-fr/download/details.aspx?id=14632

.. tip::
	.. index::
		single: ThreadStackSize

	Lorsque vous installez WampServer, la valeur par défaut de ThreadStackSize est de 1 Mo seulement, ce qui n'est pas
	suffisant pour permettre de charger le gestionnaire d'extension TYPO3. Pour corriger ce problème, ouvrez
	:file:`httpd.conf` et ajoutez :

	.. code-block:: apacheconf

		<IfModule mpm_winnt_module>
		    ThreadStackSize 8388608
		</IfModule>

	Cela aura pour effet d'allouer 8 Mo à ThreadStackSize. Ensuite redémarrez Apache.


.. _installing-python:

Installation de Python
^^^^^^^^^^^^^^^^^^^^^^

Allez sur https://www.python.org/download/releases/ et téléchargez l'installateur Python pour Windows. Au moment de la
rédaction, Python 2.7.6 est connu pour fonctionner correctement. Ensuite exécutez l'installateur et suivez les
instructions :

.. figure:: ../../../Images/python_setup.png
	:alt: Installation de Python

.. important::
	L'option "Install for all users" est nécessaire si votre serveur web s'exécute avec un autre utilisateur.

.. index::
	single: PATH; Variable d'environnement (MS Windows)
	single: Variable d'environnement

Après que l'installation s'est terminée avec succès, votre variable d'environnement ``%PATH%`` doit être mise à jour
pour rendre la commande :program:`python` disponible globalement.

Pour cela, ouvrez le Panneau de configuration > Système > Paramètres systèmes avancés > Variables d'environnement :

.. figure:: ../../../Images/environment_variables.png
	:alt: Mise à jour des variables d'environnement

Trouvez la variable système ``Path`` et modifiez-la :

.. figure:: ../../../Images/system_variables.png
	:alt: Variables d'environnement au niveau système

Vous devez rajouter le chemin vers :program:`python`. Par défaut Python est installé dans le répertoire
:file:`C:\\Python27\\`.

.. important::
	Placez le chemin à la fin de la liste existante, après avoir inséré le caractère de séparation de répertoires qui est,
	sous MS Windows, un point-virgule.

.. tip::
	Il est nécessaire de redémarrer Apache afin que TYPO3 détecte la commande :program:`python` étant donné qu'Apache ne
	charge le contenu de ``%PATH%`` qu'une seule fois, au démarrage.


.. _installing-unzip:

Installation de Unzip
^^^^^^^^^^^^^^^^^^^^^

Allez sur http://gnuwin32.sourceforge.net/packages/unzip.htm et téléchargez le programme d'installation. Ensuite lancez-le
et suivez les instrutions :

.. figure:: ../../../Images/unzip_setup.png
	:alt: Installation de l'utilitaire d'extraction UnZip

Vous devez référencer la commande :program:`unzip` dans ``%PATH%`` pour être disponible globalement. Par défaut cet
utilitaire est installé dans le répertoire :file:`C:\\Program Files (x86)\\GnuWin32\\bin`. Veuillez effectuer les mêmes
opérations que dans la section précédente.


.. _installing-tar:

Installation de Tar
^^^^^^^^^^^^^^^^^^^

Allez sur http://gnuwin32.sourceforge.net/packages/libarchive.htm (LibArchive contient BsdTar) et téléchargez le programme
d'installation. Ensuite lancez-le et suivez les instructions, comme vous l'avez fait pour :ref:`unzip <installing-unzip>` :

.. figure:: ../../../Images/libarchive_setup.png
	:alt: Installation de la bibliothèque LibArchive pour lire et écrire des flux d'archives

.. important::
	Par défaut l'utilitaire d'extraction :program:`bsdtar` est installé dans le répertoire :file:`C:\\Program Files (x86)\\GnuWin32\\bin`,
	exactement comme :program:`unzip` ; il devrait donc être automatiquement détecté comme vous avez référencé
	ce chemin dans la variable d'environnement ``%PATH%``. Au besoin, veuillez référencer un autre chemin comme
	décrit précédemment.

	Néanmoins, l'extension Sphinx recherche une commande :program:`tar` et pas :program:`bsdtar`. De ce fait, vous devriez
	soit copier :program:`bsdtar.exe` et le renommer en :program:`tar.exe` ou, mieux, créer un lien symbolique vers lui.
	Pour se faire, ouvrez une ligne de commande (CMD) *en tant qu'administrateur* :

	.. code-block:: bat

		C:\Windows\system32> cd "\Program Files (x86)\GnuWin32\bin"

		C:\Program Files (x86)\GnuWin32\bin> mklink tar.exe bsdtar.exe
		lien symbolique créé pour tar.exe <<===>> bsdtar.exe

.. note::
	.. index::
		single: Message d'erreur; Cannot fork: Function not implemented

	Si vous vous demandez pourquoi nous utilisons BsdTar au lieu de GNU Tar, voici la raison. En effet, une recherche
	rapide de :program:`tar` pour Windows conduit à GNU Tar. Cependant ce paquet n'est d'aucune utilité parce qu'il ne
	prend pas en charge les fichiers ``tar.gz`` files. En fait, il va planter avec :

	.. code-block:: bat

		C:\> Cannot fork: Function not implemented
		C:\> Error is not recoverable: exiting now

	Par ailleurs, les responsables du paquet Tar eux-mêmes recommandent d'utiliser BsdTar :

		The Win32 port can only create ``tar`` archives, but cannot pipe its output to other programs such as
		:program:`gzip` or :program:`compress`, and will not create ``tar.gz`` archives; you will have to use or simulate
		a batch pipe. BsdTar does have the ability to direcly create and manipulate ``.tar``, ``.tar.gz``, ``tar.bz2``,
		``.zip``, ``.gz`` and ``.bz2`` archives, understands the most-used options of GNU Tar, and is also much faster;
		for most purposes it is to be preferred to GNU Tar.

Félicitations ! Vous devriez maintenant être en mesure de :ref:`configurer l'extension Sphinx <configure-sphinx>` !
