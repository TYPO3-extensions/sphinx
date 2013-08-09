.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt
.. include:: Images.txt


.. _sphinx-documentation-viewer:

Visionneuse de documentation Sphinx
-----------------------------------

Cette extension fournit un autre module Backend dans la partie "Aide" :

|section_help|

Une liste déroulant tout en haut affiche les extensions utilisées qui contiennent une documentation basée sur Sphinx et
vous permet de rapidement l'afficher **localement** :

|viewer_choose_extension|

.. tip::
	La visionneuse de documentation Sphinx recharge automatiquement le dernier manuel que vous avez sélectionné et si
	vous choisissez le gabarit interactif, il va même vous ramener au chapitre que vous lisiez.


Écran d'accueil
^^^^^^^^^^^^^^^

Si aucune documentation n'est sélectionnée dans la liste déroulante :

|kickstart|

une liste d'extensions utilisées contenant uniquement un manuel OpenOffice est affichée et vous permet de facilement le
convertir en Sphinx à l'aide d'un outil en ligne disponible sur http://docs.typo3.org :

|convert-openoffice|

De façon similaire, un projet de documention Sphinx vide peut être créé pour les extensions qui n'ont pas encore de
manuel :

|kickstart-sphinx|


.. _layouts:

Gabarits
^^^^^^^^

Les manuels d'extensions peuvent être générés avec différents "gabarits" :

- **Statique:** Génère et affiche une version HTML ;

- **Interactif:** Génère et affiche une version JSON qui nécessite donc l'extension `reST Documentation Viewer (restdoc)`_.
  Par ailleurs, ce gabarit propose un :ref:`éditeur ReStructuredText <sphinx-documentation-editor>` intégré pour vous
  permettre de modifier rapidement et de recompiler un chapitre donné ;

- **PDF:** Génère et affiche une version PDF et de ce fait nécessite soit ``pdflatex`` soit ``rst2pdf`` :

  |

  |render_pdf|


Fonctionnement interne
^^^^^^^^^^^^^^^^^^^^^^

Comme les manuels d'extensions basés sur Sphinx sont destinés à être générés sur http://docs.typo3.org en utilisant la
mise en page officielle de TYPO3, ils ne fournissent pas les fichiers de configuration généraux nécessaires à un rendu
local.

Lorsque vous sélectionnez un manuel d'extension à afficher dans la liste déroulante, le processus suivant intervient :

- Si une version en cache du document principal est trouvée, la visionneuse la charge directement et ne génère pas à
  nouveau la documentation.

Sinon :

#. Un projet Sphinx vide est instancié dans le répertoire ``typo3temp/tx_sphinx/<extension-key>`` et tous les fichiers
   présents dans le répertoire ``EXT:<extension-key>/Documentation`` y sont recopiés

#. Le projet Sphinx est généré en HTML, JSON ou PDF, selon le gabarit sélectionné

#. Le rendu HTML, JSON ou PDF est copié dans le répertoire
   ``typo3conf/documentation/<extension-key>/`` ``<langue>/<format>/`` (``langue`` est toujours "default" pour l'anglais,
   sauf si une documentation multilingue est trouvée, comme c'est le cas avec cette extension pour la version française
   que vous êtes en train de lire)

#. La visionneuse charge le document principal (p. ex. ``Index.html`` si le rendu est HTML)

|

.. tip::
	Une case à cocher à droite vous permet de forcer le manuel d'extension à être généré à nouveau (ce qui recrée par
	conséquent la version mise en cache) :

	|checkbox|

.. note::
	La visionneuse de documentation Sphinx supporte deux types de manuels d'extensions :

	#. Structure de documentation standard avec un projet Sphinx complet stocké dans le répertoire
	   ``EXT:<extension-key>/Documentation/``, et un document principal nommé ``Index.rst``
	#. Simple fichier reStructuredText README comme on le voit sur Github ou Bitbucket et sauvegardé sous
	   ``EXT:<extension-key>/README.rst``

En fonction du gabarit choisi, le document principal est :

- **Statique:** Le document principal en HTML est ``typo3conf/Documentation/<extension-key>/`` ``default/html/Index.html``

- **Interactif:** Le document principal en JSON est ``typo3conf/Documentation/<extension-key>/`` ``default/json/Index.fjson``

- **PDF:** Le document principal en PDF est ``typo3conf/Documentation/<extension-key>/`` ``default/pdf/<extension-key>.pdf``


.. _documentation-viewer-custom-project:

Référencement d'un projet personnel
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Si vous avez un projet Sphinx complet (c'est-à-dire avec un fichier de configuration ``_make/conf.py``) quelque part dans
votre site. comme un projet démarré avec la :ref:`Console Sphinx <kickstart_sphinx_project>`, vous pouvez l'enregistrer
avec la visionneuse de documentation Sphinx.

En effet, nous avons implémenté notre propre signal pour :ref:`enregistrer une documentation personnelle <register-custom-documentation>`.

La liste des projets personnels est stockée dans le fichier ``typo3conf/sphinx-projects.json``. Si ce fichier n'existe
pas, vous pouvez simplement le créer avec votre éditeur de texte préféré :

.. code-block:: json

	[
	  {
	    "name": "Mon projet ABC",
	    "description": "Projet ABC qui décrit...",
	    "group": "Nom de société",
	    "key": "company.project.abc",
	    "directory": "fileadmin/restructuredtext-projects/abc/"
	  },
	  {
	    "name": "mon projet DEF",
	    "description": "Projet DEF qui décrit...",
	    "group": "Nom de société",
	    "key": "company.project.def",
	    "directory": "fileadmin/restructuredtext-projects/def/"
	  }
	]

Lorsque vous faites cela, votre projet va apparaître dans la liste des documents et vous pourrez le compiler comme la
documentation de n'importe quelle extension.
