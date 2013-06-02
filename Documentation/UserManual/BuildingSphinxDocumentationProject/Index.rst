.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt
.. include:: Images.txt


Building a Sphinx Documentation Project
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Use the module File > Sphinx Console which presents a tree of directories, go to your project and click on one the build buttons:

|project_properties|

The console will show you the output of the command.


HTML
""""

This Sphinx builder generates a standalone HTML website of your documentation into directory ``_build/html``.


JSON
""""

This Sphinx builder generates a derivate from HTML into directory ``_build/json``. You should use this builder in order to properly integrate your documentation within your TYPO3 website. Extension `reST Documentation Viewer (restdoc) <http://typo3.org/extensions/repository/view/restdoc>`_ should be used with JSON output.


LaTeX
"""""

.. index::
	single: LaTeX

This Sphinx builder generates a LaTeX project of your documentation into directory ``_build/latex``. You need a LaTeX environment to compile LaTeX projects and generate nice-looking PDF. Please consult `the TYPO3 wiki`_ for further information.


PDF
"""

.. index::
	single: PDF

If commands ``make`` and ``pdflatex`` are found on your server, then an additional build button is shown, allowing you to build the PDF version of your documentation automatically:

|build_button_pdf|


Check Links
"""""""""""

This Sphinx builder checks all links within your documentation and generates a report output.txt into directory ``_build/linkcheck/``.
