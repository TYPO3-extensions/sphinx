.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt


Installation de LaTeX sous Linux ou Mac OS X
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Votre distribution sysème ou vendeur fournit très probablement un paquet TeX comprenant LaTeX. Veuillez rechercher votre
source de logiciels usuelle pour un paquet TeX ; ou alors installez `TeX Live`_ directement.

.. _`TeX Live`: http://www.tug.org/texlive/

.. note::

	Les fichiers LaTeX produits utilisent plusieurs bibliothèques LaTeX qui peuvent ne pas être disponibles avec une
	distribution "minimale" de TeX. Pour Tex Live, les composants suivants doivent être installés en plus :

	- latex-recommended
	- latex-extra
	- fonts-recommended
	- fonts-extra

	Le composant "fonts-extra" est facultatif mais recommandé pour un rendu optimal des caractères spéciaux de certains
	manuels.

Linux Debian / Ubuntu
"""""""""""""""""""""

Vous pouvez exécuter la commande suivante pour installer les composants requis :

.. code-block:: bash

	$ sudo apt-get install texlive-base texlive-latex-recommended \
	  texlive-latex-extra texlive-fonts-recommended texlive-fonts-extra \
	  texlive-latex-base

Afin de compiler en PDF, cette extension nécessite à la fois :program:`pdflatex` (qui fait partie du paquet ``texlive-latex-extra``)
et :program:`make` :

.. code-block:: bash

	$ sudo apt-get install make

Si vous voulez être en mesure de générer du PDF en dehors de votre installation TYPO3 (donc vraisemblablement
:ref:`en utilisant sphinx-build en ligne de commande <sphinx-command-line>`, vous devez installer quelques composants
supplémentaires, :ref:`installer la fonte Share <installing-share-font>` et rendre le paquet LaTeX ``typo3`` disponible
globalement :

.. code-block:: bash

	$ sudo apt-get install python-sphinx xzdec

	$ tlmgr init-usertree
	$ sudo tlmgr update --all
	$ sudo tlmgr install ec
	$ sudo tlmgr install cm-super

	$ cd /path/to/uploads/tx_sphinx/latex.typo3/
	$ sudo mkdir /usr/share/texmf/tex/latex/typo3
	$ sudo cp typo3.sty /usr/share/texmf/tex/latex/typo3/
	$ sudo cp typo3_logo_color.png /usr/share/texmf/tex/latex/typo3/
	$ sudo texhash


Mac OS X
""""""""

Vous pouvez installer un environnement TeX Live en utilisant le paquet MacTeX_. Autrement, si vous êtes habitué à utiliser
MacPorts_, le processus est similaire à un système Debian :

.. code-block:: bash

	$ sudo port install texlive texlive-latex-extra


.. _MacTeX: http://www.tug.org/mactex/

.. _MacPorts: http://www.macports.org/
