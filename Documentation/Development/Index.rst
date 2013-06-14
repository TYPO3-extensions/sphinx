.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _development:

Development
-----------

This chapter describes some internals of the sphinx extension to let you extend it easily.

Signal and Slots
""""""""""""""""

The concept of *signals* and *slots* allows for easy implementation of the `Observer pattern <http://en.wikipedia.org/wiki/Observer_pattern>`_ in software, something similar to *hooks* in former versions of TYPO3. Its implementation in TYPO3 CMS has been backported from Flow, so please read chapter `Signals and Slots <http://docs.typo3.org/flow/TYPO3FlowDocumentation/TheDefinitiveGuide/PartIII/SignalsAndSlots.html>`_ from Flow official documentation. In short, *signals* are put into the code and call registered *slots* when run through.

Available signals and slots:

.. toctree::
	:maxdepth: 2

	SignalSlots/RegisteringCustomDocumentation


Registering your Slot to a Signal
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

To be called on a certain event, you have to register your slot to a signal using a few lines within your ``ext_localconf.php``::

	\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher')->connect(
	    $signalClassName,
	    $signalName,
	    $slotClassNameOrObject,
	    $slotMethodName,
	    $passSignalInformation
	);

.. ### BEGIN~OF~TABLE ###

.. container:: table-row

	Parameter
		$signalClassName

	Data type
		string

	Description
		Name of the class containing the signal.

		**Example:**

		::

			'Causal\\Sphinx\\Controller\\DocumentationController'

.. container:: table-row

	Parameter
		$signalName

	Data type
		string

	Description
		Name of the signal.

		**Example:**

		::

			'afterInitializeReferences'

.. container:: table-row

	Parameter
		$slotClassNameOrObject

	Data type
		string / class instance / `closure <http://en.wikipedia.org/wiki/Closure_%28computer_science%29>`_

	Description
		Either the name of the class containing the slot, or an instance of that class, or a closure.

.. container:: table-row

	Parameter
		$slotMethodName

	Data type
		string

	Description
		Name of the method to be used as a slot. Ignored if ``$slotClassNameOrObject`` is a closure.

.. container:: table-row

	Parameter
		$passSignalInformation

	Data type
		boolean

	Description
		If set to TRUE, the last argument passed to the slot will be information about the signal (``EmitterClassName::signalName``). Ignored if ``slotClassNameOrObject`` is a closure.

.. ###### END~OF~TABLE ######
