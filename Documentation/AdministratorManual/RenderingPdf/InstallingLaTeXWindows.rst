.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt
.. include:: Images.txt


Installing LaTeX on MS Windows
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Download and run the MiKTeX_ installer to setup a basic TeX/LaTeX system on your computer. You can read the section `Installing MiKTeX`_ in the MiKTeX manual, if you want to learn all the details.

.. note::

	The recommended download is the MiKTeX Basic Installer and implies that the installer will install missing packages
	on-the-fly, by fetching them online.

	Alternatively, you may choose to install a full-fledge version of MiKTeX (MiKTeX Net Installer under "Other Downloads" on the MiKTeX's website). But keep in mind that this results in a *lot larger* footprint.

|miktex_setup|

.. important::
	Option "Install MiKTeX for anyone who uses this computer" is needed if your web server runs with another user.

At step 3, the installer will ask you whether missing packages should be installed on-the-fly. We **highly** recommend you to let MiKTeX installing those missing packages without asking you. The rationale is that it ensures a smooth user experience when compiling from your TYPO3 website as interactive input cannot happen, and rendering will crash if a LaTeX package is missing on your system:

|miktex_onthefly|

.. tip::

	When you have installed MiKTeX, it is recommended that you run the update wizard in order to get the latest updates.

After the setup completed successfully, your ``%PATH%`` should have been updated to make LaTeX commands globally available. You simply will need to restart Apache in order for TYPO3 to detect them as Apache reads the ``%PATH%`` only once at startup.
