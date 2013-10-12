.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt
.. include:: Images.txt


.. _windows-setup:

Mise en place de l'environnement avec MS Windows
------------------------------------------------

À la différence de la plupart des systèmes d'exploitation :abbr:`UN*X (Unix-like)`, MS Windows ne fournit pas de façon
systématique un interpréteur Python and certains utilitaires généraux.

Comme cette extension a besoin de Python et des utilitaires d'extraction d'archives unzip et tar, les utilisateurs
MS Windows doivent tout d'abord configurer leur environnement avec le framework Python. Depuis un Windows de base,
lorsque vous exécutez le script de mise à niveau de cette extension dans le gestionnaire d'extensions en cliquant sur
l'icône d'action |update_script| vous verrez quelques messages d'erreurs :

.. index::
	single: Message d'erreur; Python interpreter was not found
	single: Message d'erreur; Unzip cannot be executed
	single: Message d'erreur; Tar cannot be executed

|environment_check_windows|

Les sections suivantes décrivent comment installer :

- :ref:`Python <installing_python>`
- :ref:`Unzip <installing_unzip>`
- :ref:`Tar <installing_tar>`

.. note::
	Ces instructions ont été testées avec :

	- Microsoft Windows 7 Professional, 64 bit edition
	- `WampServer (64 bit & PHP 5.4) 2.2E <http://www.wampserver.com/#download-wrapper>`_
	- Python 2.x

.. tip::
	.. index::
		single: Message d'erreur; MSVCR100.dll is missing from your computer

	L'installation de WampServer peut planter avec un message d'erreur similaire à :

	|msvcr100dll|

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


.. _installing_python:

Installation de Python
^^^^^^^^^^^^^^^^^^^^^^

Allez sur http://www.python.org/download/releases/ et téléchargez l'installateur Python pour Windows. Au moment de la
rédaction, Python 2.7.5 est connu pour fonctionner correctement. Ensuite exécutez l'installateur et suivez les
instructions :

|python_setup|

.. important::
	L'option "Install for all users" est nécessaire si votre serveur web s'exécute avec un autre utilisateur.

.. index::
	single: PATH; Variable d'environnement (MS Windows)
	single: Variable d'environnement

Après que l'installation s'est terminée avec succès, votre variable d'environnement ``%PATH%`` doit être mise à jour
pour rendre la commande :program:`python` disponible globalement.

Pour cela, ouvrez le Panneau de configuration > Système > Paramètres systèmes avancés > Variables d'environnement :

|environment_variables|

Trouvez la variable système ``Path`` et modifiez-la :

|system_variables|

Vous devez rajouter le chemin vers :program:`python`. Par défaut Python est installé dans le répertoire
:file:`C:\Python27\\`.

.. important::
	Placez le chemin à la fin de la liste existante, après avoir inséré le caractère de séparation de répertoires qui est,
	sous MS Windows, un point-virgule.

.. tip::
	Il est nécessaire de redémarrer Apache afin que TYPO3 détecte la commande :program:`python` étant donné qu'Apache ne
	charge le contenu de ``%PATH%`` qu'une seule fois, au démarrage.


.. _installing_unzip:

Installation de Unzip
^^^^^^^^^^^^^^^^^^^^^

Allez sur http://gnuwin32.sourceforge.net/packages/unzip.htm et téléchargez le programme d'installation. Ensuite lancez-le
et suivez les instrutions :

|unzip_setup|

Vous devez référencer la commande :program:`unzip` dans ``%PATH%`` pour être disponible globalement. Par défaut cet
utilitaire est installé dans le répertoire :file:`C:\Program Files (x86)\GnuWin32\bin`. Veuillez effectuer les mêmes
opérations que dans la section précédente.


.. _installing_tar:

Installation de Tar
^^^^^^^^^^^^^^^^^^^

.. important::
	.. index::
		single: Message d'erreur; Cannot fork: Function not implemented

	Une recherche rapide de :program:`tar` pour Windows conduit à GNU Tar sur http://gnuwin32.sourceforge.net/packages/gtar.htm.
	Cependant ce paquet n'est d'aucune utilité parce qu'il ne prend pas en charge les fichiers ``tar.gz`` files. En fait,
	il va planter avec :

	.. code-block:: bat

		C:\> Cannot fork: Function not implemented
		C:\> Error is not recoverable: exiting now

	En effet, les responsables du paquet Tar eux-mêmes recommandent d'utiliser BsdTar :

		The Win32 port can only create ``tar`` archives, but cannot pipe its output to other programs such as
		:program:`gzip` or :program:`compress`, and will not create ``tar.gz`` archives; you will have to use or simulate
		a batch pipe. BsdTar does have the ability to direcly create and manipulate ``.tar``, ``.tar.gz``, ``tar.bz2``,
		``.zip``, ``.gz`` and ``.bz2`` archives, understands the most-used options of GNU Tar, and is also much faster;
		for most purposes it is to be preferred to GNU Tar.

Allez sur http://gnuwin32.sourceforge.net/packages/libarchive.htm (LibArchive contient BsdTar) et téléchargez le programme
d'installation. Ensuite lancez-le et suivez les instructions, comme vous l'avez fait pour :ref:`unzip <installing_unzip>` :

|libarchive_setup|

.. important::
	Par défaut l'utilitaire d'extraction :program:`bsdtar` est installé dans le répertoire :file:`C:\Program Files (x86)\GnuWin32\bin`,
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

Félicitations ! Vous devriez maintenant être en mesure de :ref:`configurer l'extension Sphinx <configure-sphinx>` !
