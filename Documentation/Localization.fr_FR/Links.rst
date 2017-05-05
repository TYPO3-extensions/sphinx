.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _links:

Liens
-----

:TER:
	https://typo3.org/extensions/repository/view/sphinx

:Packagist:
	https://packagist.org/packages/causal/sphinx

:Bug tracker:
	https://forge.typo3.org/projects/extension-sphinx/issues

:Notes de version:
	https://forge.typo3.org/projects/extension-sphinx/wiki

:Dépôt Git:
	https://git.typo3.org/TYPO3CMS/Extensions/sphinx.git

:Traduction:
	https://translation.typo3.org/projects/TYPO3.ext.sphinx/

:Contact:
	`@xperseguers <https://twitter.com/xperseguers>`__


.. _links-how-to-contribute:

Comment contribuer
^^^^^^^^^^^^^^^^^^

Cette extension utilise le même flux de travail que pour le noyau de TYPO3, et se base sur https://review.typo3.org pour
le travail de relecture et d'approbation. Veuillez svp en lire plus à ce sujet dans le
`wiki TYPO3 <https://wiki.typo3.org/Contribution_Walkthrough_Life_Of_A_Patch>`__ (anglais).


.. _links-how-to-contribute-tldr:

tl;dr
"""""

.. code-block:: bash

	cd /chemin/vers/site/typo3conf/ext/

	# Remplacer la version du TER par un clône du dépôt git (ou utilisez
	# un lien symbolique si préféré)
	rm -rf sphinx
	git clone https://git.typo3.org/TYPO3CMS/Extensions/sphinx.git
	cd sphinx

	# Configuration de l'environnement
	git config user.name "Votre Nom"
	git config user.email "votre-email@exemple.com"
	git config branch.autosetuprebase remote
	# À FAIRE: remplacer "xperseguers" par votre nom d'utilisateur typo3.org
	git config url."ssh://xperseguers@review.typo3.org:29418".pushInsteadOf https://git.typo3.org

	# Installation du hook pour le commit (en cas de problème, veuillez lire
	# "Nouveau dans Gerrit ?" ci-dessous)
	# À FAIRE: remplacer "xperseguers" par votre nom d'utilisateur typo3.org
	scp -p -P 29418 xperseguers@review.typo3.org:hooks/commit-msg .git/hooks/

Vous pouvez maintenant modifier, améliorer et/ou corriger ce que vous souhaitez, puis soumettre votre patch :

.. code-block:: bash

	cd /chemin/vers/site/typo3conf/ext/sphinx
	# Ajout des changements au commit (n'ajoutez pas aveuglément tous vos changements !)
	git add .
	# Commit (ou modification d'un patch existant avec "git commit --amend")
	git commit
	# Envoi du patch pour relecture
	git push origin HEAD:refs/for/master

.. admonition:: Nouveau dans Gerrit ?
	:class: tip

	Vous aurez peut-être à configurer votre clef SSH pour Gerrit afin de pouvoir envoyer vos patches.

	#. Ouvrez https://review.typo3.org et authentifiez-vous avec votre nom d'utilisateur typo3.org.
	#. Allez sous :menuselection:`Settings --> SSH public keys` et ajoutez votre clef publique SSH.

	Veuillez consulter https://wiki.typo3.org/Contribution_Walkthrough_Environment_Setup (anglais) en cas de problème.


.. _links-how-to-contribute-rules:

Règles pour contribuer
""""""""""""""""""""""

- Il doit exister un ticket dans le bug tracker du projet expliquant le problème / l'amélioration proposée
- Les directives de codage `PSR-2`_ sont respectées
- Le message de commit respecte le `format utilisé par TYPO3`_ (la ligne "releases:" n'est pas nécessaire)
- Un seul changement logique par patch [#]_

.. _PSR-2: http://www.php-fig.org/psr/psr-2/
.. _format utilisé par TYPO3: https://wiki.typo3.org/CommitMessage_Format_(Git)


.. rubric:: Notes de bas de page

.. [#] Le terme "patch" est utilisé dans le sense de "patch set" dans Gerrit, et peut correspondre à plusieurs
   commits successifs.
