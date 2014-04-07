.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../Includes.txt


.. _postprocessing-latex:

Post-traitement LaTeX
^^^^^^^^^^^^^^^^^^^^^

Le slot :ref:`afterBuildLaTeX <postprocessing-latex-afterBuildLaTeX>` peut être utilisé pour post-traiter le fichier
LaTeX généré lorsque vous préparez votre documentation en PDF en utilisant LaTeX.


.. _postprocessing-latex-afterBuildLaTeX:

Slot : afterBuildLaTeX
""""""""""""""""""""""

Ce slot est utilisé pour pour post-traiter le fichier LaTeX généré.

Votre slot devrait implémenter une méthode de la forme :

.. code-block:: php

	public function postprocess($texFileName, /* autres paramètres */) {
	    // Votre code
	}

Le paramètre ``$texFileName`` contient le nom du fichier :file:`.tex` généré. Vous pouvez le post-traiter à votre
convenance en l'accédant et le modifiant selon vos besoins.

.. note::
	Ce slot est utilisé par cette extension pour corriger l'affichage des apostrophes simples dans les exemples de code
	et éviter qu'elles soient arrondies.
