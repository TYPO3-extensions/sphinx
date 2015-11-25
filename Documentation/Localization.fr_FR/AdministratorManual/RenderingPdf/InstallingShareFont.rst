.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt


.. _installing-share-font:

Installation de la fonte Share
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Afin de personnaliser le rendu PDF de la documentation avec LaTeX pour s'accorder à la mise en page TYPO3, nous devons
tout d'abord installer la `famille de fontes corporate Share`_ et la convertir pour qu'elle soit compatible avec LaTeX.
Les instructions sont disponibles dans le dépôt des commandes liées à TYPO3 pour Sphinx, dans le répertoire :file:`LaTeX`.

.. Liens :
.. _`famille de fontes corporate Share`: https://typo3.org/about/the-brand/style-guide/the-typo3-font/

Déplacez vous dans le répertoire :file:`latex.typo3` et convertissez la fonte Share (le mot de passe demandé
est lié à l'utilisation de :command:`sudo` ; c'est donc votre propre mot de passe) :

.. code-block:: bash

	$ cd /path/to/uploads/tx_sphinx/latex.typo3/
	$ cd font/
	$ ./convert-share.sh

.. tip::

	La famille de fontes Share complète peut être téléchargée depuis
	http://prdownloads.sourceforge.net/typo3/TYPO3_Share_Complete.zip?download.

.. note::

	**Utilisateurs MS Windows :** Veuillez utiliser le programme
	`Unicode Truetype font installer for LaTeX <http://william.famille-blum.org/software/latexttf/index.html>`__.
	Comme nous ne fournissons pas de script automatique pour vous, si vous utilisez MiKTeX, vous pouvez suivre les
	instructions du lien précédent ou celles disponibles sur http://www.radamir.com/tex/ttf-tex.htm.

	**Astuce:** Vous pouvez utiliser le script :program:`convert-share.sh` depuis une machine Linux et définir ``INSTALL=0``
	vers le début du script. Au lieu d'installer les fontes converties sur votre système, le script va uniquement les
	convertir et préparer les fichiers de correspondance (*mapping*) dans le répertoire :file:`latex.typo3/fonts/texmf/`.

Une fois convertie, la fonte est disponible comme ``typo3share`` dans vos documents LaTeX. Pour vérifier que la fonte est
correctement installée, vous pouvez créer un document LaTeX exemple, (:file:`test-font.tex`):

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

Lorsque vous ouvrez le fichier généré :file:`test-font.pdf`, vous devriez voir la fonte Share utilisée localement :

.. figure:: ../../../Images/share_font.png
	:alt: Utilisation de la famille de fontes Share avec LaTeX
