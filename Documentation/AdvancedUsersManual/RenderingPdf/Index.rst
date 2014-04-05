.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _rendering-pdf:

Rendering PDF from reStructuredText
-----------------------------------

Sphinx is using *builders* to produce output. The builder’s "name" must be given to the ``-b`` command-line option of
:program:`sphinx-build` to select a builder. For instance, when compiling as HTML, this extension invokes:

.. code-block:: bash

	$ sphinx-build -b html -c /path/to/conf.py ...

Sphinx is able to render PDF using either LaTeX as intermediate format or :program:`rst2pdf`. PDF output with
:program:`rst2pdf` is by far not as good as when using LaTeX but it has the real advantage of not requiring you to
install a full LaTeX stack on your machine.

The builder name for PDF rendering using :program:`rst2pdf` is simply ``pdf``:

.. code-block:: bash

	$ sphinx-build -b pdf -c /path/to/conf.py ...

whereas the builder name for LaTeX rendering is ``latex``:

.. code-block:: bash

	$ sphinx-build -b latex -c /path/to/conf.py ...

This latter produces a bunch of LaTeX files in the output directory. You have to specify which documents are to be
included in which LaTeX files via the `latex_documents <http://sphinx-doc.org/config.html#confval-latex_documents>`_
configuration value. There are a few configuration values that customize the output of this builder. See chapter
`Options for LaTeX output <http://sphinx-doc.org/config.html#latex-options>`_ for details.

Once the LaTeX files have been produced, the actual rendering as PDF is just a matter of compiling the LaTeX sources
with :program:`pdflatex`:

.. code-block:: bash

	$ pdflatex name-of-project

.. tip::
	PDF rendering on docs.typo3.org needs a minor adjustement to your project's configuration. Please read
	chapter :ref:`docs-typo3-org` for further information.

The remainder of this chapter gives you more insights on LaTeX:

.. toctree::
	:maxdepth: 5
	:titlesonly:

	LaTeXVsRst2pdf
	IntroductionLaTeX
	CustomizingRendering
