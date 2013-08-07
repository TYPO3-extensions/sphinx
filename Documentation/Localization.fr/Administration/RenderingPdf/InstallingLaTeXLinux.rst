.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt


Installation de LaTeX sous Linux ou Mac OS X
""""""""""""""""""""""""""""""""""""""""""""

Votre distribution sysème ou vendeur fournit très probablement un paquet TeX comprenant LaTeX. Veuillez rechercher votre
source de logiciels usuelle pour un paquet TeX ; ou alors installez `TeX Live`_ directement.

.. note::

	Les fichiers LaTeX produits utilisent plusieurs bibliothèques LaTeX qui peuvent ne pas être disponibles avec une
	distribution "minimale" de TeX. Pour Tex Live, les composants suivants doivent être installés en plus :

	- latex-recommended
	- latex-extra
	- fonts-recommended

Linux Debian
~~~~~~~~~~~~

Vous pouvez exécuter la commande suivante pour installer les composants requis :

.. code-block:: bash

	$ sudo apt-get install texlive-base texlive-latex-recommended \
	  texlive-latex-extra texlive-fonts-recommended

Afin de compiler en PDF, cette extension nécessite à la fois ``pdflatex`` (qui fait partie du paquet ``texlive-latex-extra``)
et ``make``:

.. code-block:: bash

	$ sudo apt-get install make


Mac OS X
~~~~~~~~

Vous pouvez installer un environnement Tex Live en utilisant le paquet MacTeX_. Autrement, si vous êtes habitué à utiliser
MacPorts_, le processus est similaire à un système Debian :

.. code-block:: bash

	$ sudo port install texlive texlive-latex-extra
