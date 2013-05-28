.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


What does it do?
^^^^^^^^^^^^^^^^

This extension lets you build documentation projects written with Sphinx (the Python Documentation Generator used by the TYPO3 documentation team for all official documentation) from within the TYPO3 Backend. Watch 5 min tutorial video.

Sphinx was originally created for the Python documentation and a few features are worth highlighting:

- **Output formats:** HTML, JSON (a derivate from HTML TYPO3 extension "`restdoc <http://typo3.org/extensions/repository/view/restdoc>`_" is relying on), LaTeX (for printable PDF versions), plain text, ...

- **Extensive cross-references:** semantic markup and automatic links for citations, glossary terms and similar pieces of information. Soon the official TYPO3 documentation is expected to provide resources to cross-link from your own documentation to virtually any chapter or section of any TYPO3 documentation.

- **Hierarchical structure:** easy definition of a document tree, with automatic links to siblings, parents and children

- **Automatic index:** general index of terms used in your documentation

- **Extensions:** the tool lets you extend it with your own modules

And this extension?
"""""""""""""""""""

Setting up a Sphinx environment to build documentation may be complicated for some users. This extension takes for granted that Python interpreter is available on your web server and will install and configure Sphinx **locally** (thus in your website) in a few clicks.

What can I do with a Sphinx project?
""""""""""""""""""""""""""""""""""""

A lot! And above all, if you compile your documentation as JSON, you may easily integrate it to your website. Best way is to use TYPO3 extension `reST Documentation Viewer (restdoc) <http://typo3.org/extensions/repository/view/restdoc>`_.
