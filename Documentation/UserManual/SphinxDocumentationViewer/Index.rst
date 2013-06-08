.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt
.. include:: Images.txt


.. _sphinx-documentation-viewer:

Sphinx documentation viewer
^^^^^^^^^^^^^^^^^^^^^^^^^^^

This extension provides another Backend module under section "Help":

|section_help|

A drop-down menu on top lists all loaded extensions that are featuring a Sphinx-based documentation and lets you quickly show it **locally**:

|viewer_choose_extension|

.. tip::
	The Sphinx documentation viewer automatically reloads the last manual you selected and if you choose the interactive layout, it will even bring you to the chapter you were reading.

.. _layouts:

Layouts
"""""""

Extensions' manuals may be rendered with different "layouts":

- **Static:** This renders and shows the HTML version;

- **Interactive:** This renders and shows the JSON version and as such requires extension `reST Documentation Viewer (restdoc)`_. In addition, this layout features an integrated :ref:`ReStructuredText editor <sphinx-documentation-editor>` to let you quickly edit and recompile a given chapter;

- **PDF:** This renders and shows the PDF version and as such requires ``pdflatex``:

|render_pdf|

Internals
"""""""""

As Sphinx-based extensions' manuals are meant to be rendered on http://docs.typo3.org using the TYPO3 corporate design, they do not provide the general configuration files needed to be rendered locally.

When selecting an extension's manual to be shown from the drop-down menu the following process happens:

- If a cached version of the main document is found, the viewer loads it right away and does not compile the documentation.

Otherwise:

#. An empty Sphinx project is instantiated within ``typo3temp/tx_sphinx/<extension-key>`` and all files from ``EXT:<extension-key>/Documentation`` are copied in this directory

#. The Sphinx project is built as HTML, JSON or PDF, according to selected layout

#. HTML, JSON or PDF output is copied to ``typo3conf/Documentation/<extension-key>/<format>/``

#. The viewer loads the main document (e.g., ``Index.html`` with HTML output)

.. tip::
	A checkbox on the right lets you force the extension's manual to be recompiled (thus recreating the cached version):

	|checkbox|

According to the selected layout, the main document is:

- **Static:** Main document of HTML output is ``typo3conf/Documentation/<extension-key>/html/Index.html``

- **Interactive:** Main document of JSON output is ``typo3conf/Documentation/<extension-key>/json/Index.fjson``

- **PDF:** Main document of PDF output is ``typo3conf/Documentation/<extension-key>/pdf/<extension-key>.pdf``
