.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt
.. include:: Images.txt


.. _introduction:

Introduction
============


.. _what-it-does:

What does it do?
----------------

.. only:: latex or missing_sphinxcontrib_youtube

	This extension lets you build documentation projects written with Sphinx_ (the Python Documentation Generator used
	by the TYPO3 documentation team for all official documentation) from within the TYPO3 Backend.
	Watch `5 min tutorial video`_.

.. only:: html and not missing_sphinxcontrib_youtube

	This extension lets you build documentation projects written with Sphinx_ (the Python Documentation Generator used
	by the TYPO3 documentation team for all official documentation) from within the TYPO3 Backend:

	.. youtube:: YeGqHMDT7R8
		:width: 100%

	|

Sphinx was originally created for the Python documentation and a few features are worth highlighting:

- **Output formats:** HTML, JSON (a derivate from HTML TYPO3 extension "`restdoc <http://typo3.org/extensions/repository/view/restdoc>`_" is relying on), LaTeX (for printable PDF versions), plain text, ...

- **Extensive cross-references:** semantic markup and automatic links for citations, glossary terms and similar pieces of information. For instance, the official TYPO3 documentation provides resources to cross-link from your own documentation to virtually any chapter or section of any TYPO3 documentation. Please consult page `Tips and Tricks`_ in the TYPO3 wiki for more information.

- **Hierarchical structure:** easy definition of a document tree, with automatic links to siblings, parents and children

- **Automatic index:** general index of terms used in your documentation

- **Extensions:** the tool lets you extend it with your own modules

.. Links:
.. _`5 min tutorial video`: http://www.youtube.com/watch?v=YeGqHMDT7R8

.. _Sphinx: http://sphinx-doc.org/

.. _`the TYPO3 wiki`: http://wiki.typo3.org/Rendering_reST

.. _`Tips and Tricks`: http://wiki.typo3.org/Tips_and_Tricks_%28reST%29


And this extension?
^^^^^^^^^^^^^^^^^^^

Setting up a Sphinx environment to build documentation may be complicated for some users. This extension takes for granted that Python interpreter is available on your web server and will install and configure Sphinx **locally** (thus in your website) in a few clicks.

In addition, this extension comes with a few goodies such as:

- Backend documentation viewer
- Backend module to kickstart a Sphinx documentation project
- Backend module to compile a Sphinx project
- Integrated reStructuredText editor
- Wizard to convert an OpenOffice document (``manual.sxw``) to a Sphinx project (using an online tool on http://docs.typo3.org)


What can I do with a Sphinx project?
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

A lot! And above all, if you compile your documentation as JSON, you may easily integrate it to your website. Best way
is to use TYPO3 extension `Sphinx/reStructuredText Documentation Viewer (restdoc)`_.

.. _`Sphinx/reStructuredText Documentation Viewer (restdoc)`: http://typo3.org/extensions/repository/view/restdoc


.. _screenshots:

Screenshots
-----------

|project_wizard_overview|

|

Build an existing Sphinx documentation project
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

|mod1_overview|

|

Build PDF with pdflatex
^^^^^^^^^^^^^^^^^^^^^^^

|build_buttons|

|

Render and browse other extension manuals locally
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

|viewer|
