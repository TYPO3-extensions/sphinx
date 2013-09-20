.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt


.. _docs-typo3-org:

Rendu sur docs.typo3.org
------------------------

Lorsque vous publiez votre extension sur le :abbr:`TER (TYPO3 Extension Repository)`, la documentation
Sphinx/reStructuredText associée est automatiquement compilée et publiée sur le site http://docs.typo3.org à l'URL
``http://docs.typo3.org/typo3cms/extensions/<extension-key>/``.

Par exemple, cette documentation est publiée sur http://docs.typo3.org/typo3cms/extensions/sphinx/.

De plus, une archive zip est créée automatiquement pour chaque combinaison de version et de langue et contient une copie
du rendu HTML (aussi connu sous le nom de "gabarit statique" dans cette extension) et son équivalent PDF (si le rendu
PDF a été activé, voir :ref:`ci-après <docs-typo3-org-pdf>`). Les archives sont stockés sous
``http://docs.typo3.org/typo3cms/extensions/<extension-key>/packages/`` ``<extension-key>-<version>-<language>.zip``.
Exemples :

- http://docs.typo3.org/typo3cms/extensions/sphinx/packages/sphinx-1.1.0-default.zip
- http://docs.typo3.org/typo3cms/extensions/sphinx/packages/sphinx-1.1.0-fr-fr.zip

La liste des archives disponibles peut être récupérée simplement depuis l'URL
http://docs.typo3.org/typo3cms/extensions/sphinx/packages/packages.xml (vous pouvez bien évidemment remplacer le segment
``/sphinx/`` par n'importe quelle autre clé d'extension).

.. caution::
	Les noms de fichiers et les URIs sont générés en minuscules et avec des tirets en lieu et place de traits de
	soulignement. Cela signifie qu'une documentation avec la langue (ou pour être exacte la *locale*) ``fr_FR`` sera
	en fait accessible en utilisant ``fr-fr``.


Titre, mention de copyright et version
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. index::
	single: Settings.yml (format)

Un projet Sphinx valide pour un manuel d'extension doit contenir un fichier de configuration ``Settings.yml`` au même
niveau que le document principal ``Index.rst``. Ce fichier est votre passe pour surcharger les paramètres par défaut du
fichier de configuration Sphinx réel ``conf.py`` qui ne fait pas partie de votre projet (étant donné qu'il va contenir
des paramètres liés à l'environnement de compilation sur http://docs.typo3.org). Au lieu de ça, ce fichier de
configuration YAML vous permet de définir certaines options de projet.

De façon similaire, cette extension s'occupe de lire les options de ``Settings.yml``, ce qui assure une uniformité entre
le travail local sur votre manuel d'extension et son déploiement automatique sur http://docs.typo3.org.

Un fichier standard ``Settings.yml`` devrait définir certaines informations générales de projet :

.. code-block:: yaml
	:linenos:

	conf.py:
	  copyright: 2013
	  project: Générateur et visionneuse de documentation Sphinx Python
	  version: 1.2
	  release: 1.2.0

project
	Le nom du projet de documentation.

copyright
	Une mention de copyright du genre ``2013, Nom de l'auteur``.

	.. tip::
		Au sein de la documention TYPO3 officielle, nous ne renseignons généralement que l'année ou l'intervalle
		d'années comme ``2013`` ou ``2010-2013``.

version
	La version majeure du projet, utilisé comme remplacement pour ``|version|``. Par exemple, pour la documnetation
	TYPO3, ça pourrait être ``6.2``.

release
	La version complète du projet, utilisé comme remplacement pour ``|release|``. Par exemple, pour la documentation
	TYPO3, ça pourrait être ``6.2.0rc1``.

	Si vous n'avez pas besoin de séparation entre ``version`` et ``release``, vous pouvez simplement définir les deux
	options avec la même valeur.

	.. tip::
		L'auteur de l'extension est bien évidemment libre de choisir son schéma de numérotation des versions mais les
		bonnes pratiques sont de suivre les mêmes règles que pour le noyau TYPO3 et de ne pas introduire de changements
		majeurs ou de nouvelles fonctionalités pour les sorties de nouvelles versions de correction de votre extension
		(lorsque seul le dernier chiffre de la version change).

		Puisque les auteurs d'extensions ont une grande chance d'oublier de mettre à jour la version avant la publication
		de leur extension sur le TER, le moteur de rendu sur http://docs.typo3.org surcharge automatiquement les
		paramètres *version* et *release* en utilisant la valeur effective telle que vue sur le TER.


.. _docs-typo3-org-pdf:

Rendu PDF
^^^^^^^^^

La version PDF de votre documentation est générée en utilisant le générateur LaTeX de Sphinx (cf. :ref:`rendering-pdf`
au besoin) et doit être explicitement activé pour votre extension. Pour se faire, ouvrez le fichier ``Settings.yml``
(à la racine de votre dossier de documentation) et assurez-vous qu'il contient les options de configuration suivantes
(lignes 6 à 15) :

.. code-block:: yaml
	:linenos:

	conf.py:
	  copyright: 2013
	  project: Générateur et visionneuse de documentation Sphinx Python
	  version: 1.1
	  release: 1.1.0
	  latex_documents:
	  - - Index
	    - sphinx.tex
	    - Générateur et visionneuse de documentation Sphinx Python
	    - Xavier Perseguers
	    - manual
	  latex_elements:
	    papersize: a4paper
	    pointsize: 10pt
	    preamble: \usepackage{typo3}

Les lignes 7 à 11 définissent les options pour la valeur ``latex_documents`` qui détermine comment regrouper
la structure du document sous forme de fichiers LaTeX. C'est une liste de tuples : ``startdocname``, ``targetname``,
``title``, ``author``, ``documentclass``, où les éléments sont :

startdocname
	Le nom du document qui est la "racine" des fichiers LaTeX. Tous les documents qui sont référencés dans sa table des
	matières seront également inclus dans le fichier LaTeX.

	.. warning::
		Utilisez systématiquement ``Index`` ici.

targetname
	Le nom de fichier LaTeX dans le répertoire de sortie.

	.. warning::
		Utilisez systématiquement votre clé d'extension suivie de ``.tex`` ici.

title
	Le titre de document LaTeX. Il est inséré comme du contenu LaTeX, donc les caractères spéciaux comme la barre oblique
	inversée ou l'esperluette doivent être représentés par les commandes LaTeX correspondantes s'ils ont besoin d'être
	interprétés comme tels.

author
	L'auteur du document LaTeX. Les mêmes considérations que pour le *titre* s'appliquent. Utilisez ``\and`` (**également
	en français**) pour séparer des auteurs multiples, comme dans : ``'John \and Sarah'``.

documentclass
	En principe, soit ``manual`` soit ``howto`` (fournis par Sphinx).

	.. tip::
		Pour garder le design TYPO3, vous devriez utiliser systématiquement ``manual`` ici.

Les lignes 12 à 15 devraient être recopiées telles quelles. La ligne 15 est en fait le "déclencheur" de rendu PDF.

Lorsque le rendu PDF est activé, votre documentation est générée automatiquement sur http://docs.typo3.org à l'URL
``http://docs.typo3.org/typo3cms/extensions/<extension-key>/_pdf/``. Par exemple :
http://docs.typo3.org/typo3cms/extensions/sphinx/_pdf/.

Veuillez lire le chapitre :ref:`customizing-rendering` pour de plus amples informations sur les options de configuration
LaTeX.


.. _docs-typo3-org-multilingual:

Documentation multilingue
^^^^^^^^^^^^^^^^^^^^^^^^^

.. index::
	single: Manuel multilingue

Les manuels d'extensions multilingues sont supportés à la fois par cette extension et par http://docs.typo3.org. Si vous
souhaitez traduire votre documentation, démarrez un nouveau projet Sphinx complet (y.c. ``Settings.yml``) dans le
répertoire ``Documentation/Localization.<locale>``.

.. tip::
	Vous pouvez réutiliser vos ressources comme ``Includes.txt`` ou les images de votre documentation principale dans
	le répertoire ``Documentation`` mais pas dans l'autre sens, c'est-à-dire que vous ne pouvez pas réutiliser des
	ressources spécifiques à un manuel traduit dans votre manuel principal (anglais).

Locales
"""""""

.. index::
	single: Locales

La liste des langues supportées par Sphinx est :

=======  ========================
Prefixe  Nom
=======  ========================
bn       Bengali
ca       Catalan
cs       Tchèque
da       Danois
de       Allemand
es       Espagnol
et       Estonien
eu       Basque
fa       Iranien
fi       Finnois
fr       Français
hr       Croate
hu       Hongrois
id       Indonésien
it       Italien
ja       Japonais
ko       Coréen
lt       Lithuanien
lv       Letton
mk       Macédonien
nb_NO    Norvégien
ne       Nepalien
nl       Néerlandais
pl       Polonais
pt_BR    Portugais du Brésil
ru       Russe
si       Cinghalais
sk       Slovaque
sl       Slovène
sv       Suédois
tr       Turc
uk_UA    Ukrainien
zh_CN    Chinois simplifié
zh_TW    Chinois traditionnel
=======  ========================

Mis à part pour les quelques préfixes qui sont déjà des "locales", http://docs.typo3.org s'attend à avoir une locale et
pas un code de langue. Par conséquent, assurez-vous d'*étendre* le préfixe en conséquence. Par exemple, une
documentation française (préfixe ``fr``) devrait être étendue soit en ``fr_FR`` (français de France) ou en ``fr_CA``
(français du Canada).

Votre manuel d'extension traduit sera généré sur http://docs.typo3.org/typo3cms/extensions/sphinx/fr-fr/ (HTML) et
http://docs.typo3.org/typo3cms/extensions/sphinx/fr-fr/_pdf/ (PDF).

.. caution::
	Les noms de fichiers et les URIs sont générés en minuscules et avec des tirets en lieu et place de traits de
	soulignement. Cela signifie qu'une documentation avec la locale ``fr_FR`` sera en fait accessible en
	utilisant ``fr-fr``.


.. _docs-typo3-org-crosslink:

Références croisées vers une autre documentation
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Par défaut, Sphinx sur http://docs.typo3.org vous permet de créer des références croisées vers les manuels officiels
et donc de rechercher les références dans un jeu externe de références en préfixant la cible du lien de façon appropriée.
Un lien tel que ``:ref:`stdWrap en détails <t3tsref:stdwrap>``` va créer un lien vers la version stable de la
"Référence TypoScript" officielle de TYPO3, vers le chapitre "stdWrap" :

* :ref:`stdWrap en détails <t3tsref:stdwrap>`

Derrière les coulisses, voici ce qui se passe :

- Chaque rendu HTML Sphinx crée un fichier nommé ``objects.inv`` qui contient une correspondance entre les ancres et les
  URIs relatives à la racine des fichiers HTML.

- Les projets qui utilisent l'extension Intersphinx peuvent spécifier l'emplacement de tels fichiers de correspondance
  grâce à l'option de configuration ``intersphinx_mapping``. La correspondance sera ensuite utilisée pour résoudre des
  références à des objets sinon manquants vers une autre documentation.

La liste des manuels officiels et leurs préfixes correspondants peut être trouvée sur
http://docs.typo3.org/typo3cms/Index.html.

.. caution::
	Bien que Sphinx sur http://docs.typo3.org vous permette de façon automatique et magique de créer des références
	croisées vers les manuels officiels, il est considéré comme mauvaise pratique de se baser dessus. Il est même
	question de changer ce comportement par défaut. C'est pourquoi vous devriez **toujours** charger explicitement les
	références vers lesquelles vous souhaitez créer des références croisées, comme décrit ci-après.

Vous êtes en mesure de créer des liens vers d'autres documentations de http://docs.typo3.org (ou ailleurs) en configurant
la correspondance Intersphinx dans ``Settings.yml``. Pour se faire, ajoutez des options de configuration (lignes 6 à 9) :

.. code-block:: yaml
	:linenos:

	conf.py:
	  copyright: 2013
	  project: Générateur et visionneuse de documentation Sphinx Python
	  version: 1.2
	  release: 1.2.0
	  intersphinx_mapping:
	    restdoc:
	    - http://docs.typo3.org/typo3cms/extensions/restdoc/
	    - null

Cela aura pour effet d'enregistrer le préfixe ``restdoc`` et vous permettra de créer des références croisées vers
n'importe quel chapitre de la documentation de l'extension *reST Documentation Viewer*. Par exemple sont journal des
modifications avec ``:ref:`Journal des modifications pour EXT:restdoc <restdoc:changelog>```. Par convention, vous
devriez la clé d'extension comme préfixe vers d'autres manuels :

* :ref:`Journal des modifications pour EXT:restdoc <restdoc:changelog>`

.. caution::
	Dès lors que vous définissez des correspondances Intersphinx dans le fichier de configuration ``Settings.yml``,
	la liste de références vers les manuels officiels est supprimée. Si vous souhaitez créer des références croisées
	vers une documentation officielle TYPO3 en plus d'autres documentation arbitraires, assurez-vous de définir les
	correspondances correspondantes également.

.. tip::
	Vous pouvez tirer partie de l'API de cette extension pour récupérer la liste des références de n'importe quelle
	extension dont la documentation est générée sur http://docs.typo3.org en invoquant la
	méthode ``getIntersphinxReferences()`` :

	.. code-block:: php

		$extensionKey = 'sphinx';
		$references = \Causal\Sphinx\Utility\GeneralUtility::getIntersphinxReferences($extensionKey);
		print_r($references);
