.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt
.. include:: Images.txt


.. _installing_share_font:

Installation de la fonte Share
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Afin de personnaliser le rendu PDF de la documentation avec LaTeX pour s'accorder à la mise en page TYPO3, nous devons
tout d'abord installer la `famille de fontes corporate Share`_ et la convertir pour qu'elle soit compatible avec LaTeX.
Les instructions sont disponibles dans le dépôt des commandes liées à TYPO3 pour Sphinx, dans le répertoire ``LaTeX``.

.. Liens :
.. _`famille de fontes corporate Share`: http://typo3.org/the-brand/style-guide/the-typo3-font/

Au lieu de simplement récupérer ce répertoire ``LaTeX`` depuis le dépôt officiel, nous vous suggérons de remplacement
complètement le répertoire ``RestTools`` et d'utiliser un clone du dépôt git complet :

.. code-block:: bash

	$ cd /path/to/uploads/tx_sphinx/
	$ sudo rm -rf RestTools
	$ git clone git://git.typo3.org/Documentation/RestTools.git

Déplacez vous ensuite dans le répertoire ``RestTools/LaTeX`` et convertissez la fonte Share (le mot de passe demandé
est lié à l'utilisation de ``sudo`` ; c'est donc votre propre mot de passe) :

.. code-block:: bash

	$ cd /path/to/uploads/tx_sphinx/RestTools/LaTeX/
	$ cd font/
	$ ./convert-share.sh

.. tip::

	La famille de fontes Share complète peut être téléchargée depuis
	http://prdownloads.sourceforge.net/typo3/TYPO3_Share_Complete.zip?download.

.. note::

	**Utilisateurs MS Windows :** Comme nous ne fournissons pas de script automatique pour vous, si vous utilisez MiKTeX,
	vous pouvez suivre les instructions sur http://www.radamir.com/tex/ttf-tex.htm.

	**Astuce:** Vous pouvez utiliser le script ``convert-share.sh`` depuis une machine Linux et définir ``INSTALL=0``
	vers le début du script. Au lieu d'installer les fontes converties sur votre système, le script va uniquement les
	convertir et préparer les fichiers de correspondance (*mapping*) dans le répertoire ``RestTools/LaTeX/fonts/texmf/``.

Une fois convertie, la fonte est disponible comme ``typo3share`` dans vos documents LaTeX. Pour vérifier que la fonte est
correctement installée, vous pouvez créer un document LaTeX exemple, (``test-font.tex``):

.. code-block:: latex

	\documentclass{article}

	\RequirePackage[utf8]{inputenc}
	\RequirePackage[T1]{fontenc}

	%% TYPO3 font
	\newcommand\sharefont{\fontfamily{typo3share}\selectfont}

	\begin{document}

	We chose to distribute licenses as they support the vision and
	mission of the TYPO3 project:

	"{\sharefont Inspiring People to \textbf{\emph{Share}}}" and
	"Jointly Innovate Excellent Free Software Enabling People to
	Communicate"

	\end{document}

et le compiler avec :

.. code-block:: bash

	$ pdflatex test-font

Lorsque vous ouvrez le fichier généré ``test-font.pdf``, vous devriez voir la fonte Share utilisée localement :

|share_font|
