.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


Installing LaTeX on Linux or Mac OS X
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Your system distribution or vendor has probably provided a TeX system including LaTeX. Check your usual software source
for a TeX package; otherwise install `TeX Live`_ directly.

.. _`TeX Live`: http://www.tug.org/texlive/

.. note::

	The produced LaTeX file uses several LaTeX packages that may not be present in a "minimal" TeX distribution installation. For TeX Live, the following packages need to be installed:

	- latex-recommended
	- latex-extra
	- fonts-recommended
	- fonts-extra

	Package "fonts-extra" is optional but recommended for best rendering of special symbols in some manuals.

Linux Debian / Ubuntu
"""""""""""""""""""""

You can issue following command to install required components:

.. code-block:: bash

	$ sudo apt-get install texlive-base texlive-latex-recommended \
	  texlive-latex-extra texlive-fonts-recommended texlive-fonts-extra \
	  texlive-latex-base

In order to compile as PDF, this extension requires both :program:`pdflatex` (included in
package ``texlive-latex-extra``) and :program:`make`:

.. code-block:: bash

	$ sudo apt-get install make

If you want to be able to render PDF outside of your TYPO3 installation (thus typically
:ref:`using sphinx-build in the command line <sphinx-command-line>`, you need to install a few other packages,
:ref:`install the Share font <installing-share-font>` and make the ``typo3`` LaTeX package available globally:

.. code-block:: bash

	$ sudo apt-get install python-sphinx xzdec

	$ tlmgr init-usertree
	$ sudo tlmgr update --all
	$ sudo tlmgr install ec
	$ sudo tlmgr install cm-super

	$ cd /path/to/uploads/tx_sphinx/RestTools/LaTeX/
	$ sudo mkdir /usr/share/texmf/tex/latex/typo3
	$ sudo cp typo3.sty /usr/share/texmf/tex/latex/typo3/
	$ sudo cp typo3_logo_color.png /usr/share/texmf/tex/latex/typo3/
	$ sudo texhash


Mac OS X
""""""""

You can install the TeX Live environment using package MacTeX_. Alternatively, if you are used to MacPorts_, the process is similar to a Debian system:

.. code-block:: bash

	$ sudo port install texlive texlive-latex-extra


.. _MacTeX: http://www.tug.org/mactex/

.. _MacPorts: http://www.macports.org/
