.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt
.. include:: Images.txt


.. _kickstart_sphinx_project:

Creating and building a Sphinx documentation project
----------------------------------------------------

.. note::

	This section describes the creation and build of a Sphinx documentation project using the Backend
	module "Sphinx Console". Since version 1.1.0, extension authors may kickstart a Sphinx-based documentation
	right from the welcome screen of Backend module :ref:`sphinx-documentation-viewer`. It even features
	a link to convert an existing OpenOffice-based manual to Sphinx.

Use the module File > Sphinx Console which presents a tree of directories, choose an empty directory and use the wizard that is automatically shown to create a new Sphinx documentation project:

|

|project_wizard|

A blank Sphinx project will be created. You may then edit the configuration file :file:`conf.py` to fit your needs.

To build a Sphinx project, choose a director containing a documentation project and click on one the build buttons:

|

|project_properties|

The console will show you the output of the command.

.. tip::
	You may register your custom project with the :ref:`sphinx-documentation-viewer`. To do so, please follow
	the :ref:`instructions <documentation-viewer-custom-project>`.


HTML
^^^^

This Sphinx builder generates a standalone HTML website of your documentation into directory :file:`_build/html`.


JSON
^^^^

This Sphinx builder generates a derivate from HTML into directory :file:`_build/json`. You should use this builder in
order to properly integrate your documentation within your TYPO3 website.
Extension `reST Documentation Viewer (restdoc) <http://typo3.org/extensions/repository/view/restdoc>`_ should be used
with JSON output.


LaTeX
^^^^^

.. index::
	single: LaTeX

This Sphinx builder generates a LaTeX project of your documentation into directory :file:`_build/latex`. You need a
LaTeX environment to compile LaTeX projects and generate nice-looking PDF. Please consult chapter :ref:`rendering-pdf`
for further information.


PDF
^^^

.. index::
	single: PDF

If commands :program:`make` and :program:`pdflatex` are found on your server (or if you configured :program:`rst2pdf`),
then an additional build button is shown, allowing you to build the PDF version of your documentation automatically:

|build_button_pdf|


Check Links
^^^^^^^^^^^

This Sphinx builder checks all links within your documentation and generates a report :file:`output.txt` into
directory :file:`_build/linkcheck/`.
