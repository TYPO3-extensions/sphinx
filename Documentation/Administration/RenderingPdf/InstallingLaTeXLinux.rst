.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


Installing LaTeX on Linux or Mac OS X
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Your system distribution or vendor has probably provided a TeX system including LaTeX. Check your usual software source for a TeX package; otherwise install `TeX Live`_ directly.

.. note::

	The produced LaTeX file uses several LaTeX packages that may not be present in a "minimal" TeX distribution installation. For TeX Live, the following packages need to be installed:

	- latex-recommended
	- latex-extra
	- fonts-recommended

Linux Debian
""""""""""""

You can issue following command to install required components:

.. code-block:: bash

	$ sudo apt-get install texlive-base texlive-latex-recommended \
	  texlive-latex-extra texlive-fonts-recommended

In order to compile as PDF, this extension requires both ``pdflatex`` (included in package ``texlive-latex-extra``) and ``make``:

.. code-block:: bash

	$ sudo apt-get install make


Mac OS X
""""""""

You can install the TeX Live environment using package MacTeX_. Alternatively, if you are used to MacPorts_, the process is similar to a Debian system:

.. code-block:: bash

	$ sudo port install texlive texlive-latex-extra
