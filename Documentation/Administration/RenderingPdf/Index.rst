.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _rendering_pdf:

Rendering PDF
^^^^^^^^^^^^^

Sphinx is using *builders* to produce output. The builder’s "name" must be given to the ``-b`` command-line option of ``sphinx-build`` to select a builder. For instance, when compiling as HTML, this extension invokes:

.. code-block:: bash

	$ sphinx-build -b html -c /path/to/conf.py ...

Sphinx is able to render PDF using either LaTeX as intermediate format or ``rst2pdf`` which is available off http://rst2pdf.ralsina.com.ar/.

This chapter describes how to install LaTeX as the PDF output is much prettier and professional. One drawback of LaTeX is that it can only be installed globally on your system; this is the reason why this extension does not provide a wizard in Extension Manager to install and configure it automatically for you.

If you happen to successfully install and configure ``rst2pdf`` locally for your own account, please drop me a note so that I can extend this manual and potentially automate the process for other users.

The builder name for LaTeX rendering is ``latex``:

.. code-block:: bash

	$ sphinx-build -b latex -c /path/to/conf.py ...

This builder produces a bunch of LaTeX files in the output directory. You have to specify which documents are to be included in which LaTeX files via the `latex_documents <http://sphinx-doc.org/config.html#confval-latex_documents>`_ configuration value. There are a few configuration values that customize the output of this builder, see the chapter `Options for LaTeX output <http://sphinx-doc.org/config.html#latex-options>`_ for details.

Once the LaTeX files have been produced, the actual rendering as PDF is just a matter of compiling the LaTeX sources with ``pdflatex``.

.. toctree::
	:maxdepth: 5
	:titlesonly:
	:glob:

	IntroductionLaTeX
	InstallingLaTeXLinux
	InstallingLaTeXWindows
