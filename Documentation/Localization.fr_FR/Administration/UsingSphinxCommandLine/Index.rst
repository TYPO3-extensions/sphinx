.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt


Utilisation de Sphinx depuis la ligne de commande
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

.. index::
	single: Ligne de commande
	single: Lien symbolique

Bien que cette extension soit principalement destinée à fournir un environnement complet de rendu de la documentation
depuis le Backend de TYPO3, il est probable que certains utilisateurs vont l'installer plutôt pour configurer rapidement
environnement Sphinx sur leur machine locale.

Cette extension crée automatiquement des scripts de raccourci dans le répertoire
``EXT:sphinx/Resources/Private/sphinx/bin/`` :

.. code-block:: none

	.
	|-- sphinx-build -> sphinx-build-1.2b1
	|-- sphinx-build-1.0.8
	|-- sphinx-build-1.1.3
	|-- sphinx-build-1.2b1
	|-- sphinx-quickstart -> sphinx-quickstart-1.2b1
	|-- sphinx-quickstart-1.0.8
	|-- sphinx-quickstart-1.1.3
	`-- sphinx-quickstart-1.2b1

.. index::
	pair: PATH; Variable d'environnement

La version sélectionnée de Sphinx (le script sans numéro de version) est celle que vous avez choisie dans le
gestionnaire d'extensions.

.. tip::
	Les fichiers Makefile font généralement référence à ``sphinx-build`` pour générer votre documentation. Si vous
	envisagez d'utiliser Sphinx manuellement depuis la ligne de commande, vous devriez ajouter le répertoire
	``EXT:sphinx/Resources/Private/sphinx/bin`` à votre variable d'environnement ``PATH``.
