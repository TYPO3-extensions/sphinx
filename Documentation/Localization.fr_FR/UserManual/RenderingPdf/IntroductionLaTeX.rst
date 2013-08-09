.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt
.. include:: Images.txt


Présentation de LaTeX
^^^^^^^^^^^^^^^^^^^^^

TeX et les programmes associés comme LaTeX (formaté comme |LaTeX_logo| et prononcé "La-tek") est un système de mise en
page pour ordinateur. Il est bien connu pour son habileté avec le contenu mathématique et scientifique (LaTeX est utilisé
comme principale méthode d'affichage des formules sur Wikipedia) et d'autres travaux typographiques difficiles tels que
des documents longs ou compliqués et des ouvrages multilingues.

Les systèmes TeX produisent un résultat -- sur papier ou sur un écran -- de la plus haute qualité typographique. Même
avec des documents simples vous obtenez un meilleur résultat qu'avec un traitement de textes. Comparez
`ces exemples de texte simple <http://www.ctan.org/tex/zen.pdf>`_ tirés de *Zen in the Art of Archery* de Herigels une
fois avec le traitement de texte *Word*, et une fois avec *TeX*. Ces exemples sont courts et les différences typographiques
sont subtiles mais même un non-spécialiste aura le sentiment que la page TeX est plus jolie. Par exemple, la page issue
du traitement de textes a quelques lignes avec de grands espaces entre les mots et certaines lignes contiennent au
contraire trop de mot pour la largeur de colonne ; comparez la deuxième ligne du second paragraphe avec la troisième. Le
rendu TeX est meilleur.

LaTeX est conçu pour fournir un langage haut-niveau pour la puissance de TeX. LaTeX est donc essentiellement un
ensemble de macros TeX et un programme pour traiter les documents LaTeX. Puisque les commandes de formatage TeX sont très
bas niveau, il est fondamentalement beaucoup plus facile pour l'utilisateur final d'utiliser LaTeX.

De même que reStructuredText, LaTeX se fonde sur l'idée qu'il est judicieux de laisser la mise en page à des designers
de documents, et de laisser les auteurs se concentrer sur la rédaction à proprement parler. Avec reStructuredText, vous
créez un document simple avec :

.. code-block:: rest

	=========================================================
	À propos des catégories cartésienness et du prix des œufs
	=========================================================

	:auteur: Philippe Simon
	:date: Septembre 1994

	Mon premier chapitre
	====================

	Bonjour le monde !

et en LaTeX vous créez ce même document avec :

.. code-block:: latex

	\documentclass{article}
	\title{À propos des catégories cartésienness et du prix des œufs}
	\author{Philippe Simon}
	\date{Septembre 1994}
	\begin{document}
	\maketitle
	\section{Mon premier chapitre}
	Bonjour le monde !
	\end{document}

Historique
""""""""""

LaTeX se base sur le langage de mise en page TeX_ de `Donald E. Knuth`_ et certaines extensions. LaTeX a été développé
pour la première fois en 1985 par `Leslie Lamport`_, et est maintenant maintenu et développé par le `LaTeX3 Project`_.
Il est intéressant de mentionner que la première publication de TeX remonte à 1978 et que la version stable actuelle
porte le numéro 3.1415926 et date de mars 2008 !

Vous trouvez ci-dessous quelques interviews Donald E. Knuth (en anglais) :

- `The importance of stability for TeX <http://www.webofstories.com/play/donald.knuth/68>`_ (and the fundamental difference between the GNU public license and TeX)
- `Deciding to make my own typesetting program <http://www.webofstories.com/play/donald.knuth/68>`_
- `Working on my own typesetting program (Part 1) <http://www.webofstories.com/play/donald.knuth/52>`_
- `Working on my own typesetting program (Part 2) <http://www.webofstories.com/play/donald.knuth/53>`_
- `Research into the history of typography <http://www.webofstories.com/play/donald.knuth/54>`_
