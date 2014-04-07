.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _postprocessing-latex:

Post-processing LaTeX
^^^^^^^^^^^^^^^^^^^^^

The slot :ref:`afterBuildLaTeX <postprocessing-latex-afterBuildLaTeX>` may be used to post-process the generated LaTeX
file when rendering your documentation as PDF using LaTeX.


.. _postprocessing-latex-afterBuildLaTeX:

Slot: afterBuildLaTeX
"""""""""""""""""""""

This slot is used to post-process the generated LaTeX file.

Your slot should implement a method of the form:

.. code-block:: php

	public function postprocess($texFileName, /* additional parameters */) {
	    // Custom code
	}

Parameter ``$texFileName`` contains the name of the generated :file:`.tex` file that you may post-process by directly
accessing and rewriting it as you want.

.. note::
	This slot is being used by this extension to fix the curly single quote in source code and use a straight
	one instead.
