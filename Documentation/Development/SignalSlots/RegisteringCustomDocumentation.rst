.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt
.. include:: Images.txt


Registering Custom Documentation
""""""""""""""""""""""""""""""""

The two slots :ref:`afterInitializeReferences <custom-documentation-afterInitializeReferences>` and :ref:`renderUserDocumentation <custom-documentation-renderUserDocumentation>` should be used to register and render your own documentation. Please see :ref:`sample code below <custom-documentation-sample>`.


.. _custom-documentation-afterInitializeReferences:

Slot: afterInitializeReferences
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

This slot is used to register additional entries in the drop-down list of available documentations.

Your slot should implement a method of the form::

	public function postprocessReferences(array &$references) {
	    // Custom code
	}

Parameter ``$references`` is an bi-dimensional array containing the list of local, global and system extensions with a Sphinx-based documentation. As the array is passed by reference, you may post-process the array and add/remove/modify existing entries.


.. _custom-documentation-renderUserDocumentation:

Slot: renderUserDocumentation
^^^^^^^^^^^^^^^^^^^^^^^^^^^^

This slot is used to render your custom documentation and return the URL of the master page.

Your slot should implement a method of the form::

	public function render($identifier, $layout, $force, &$documentationUrl) {
		// Custom code
	}


.. _custom-documentation-sample:

Sample code
^^^^^^^^^^^

This sample code will register a custom documentation and simply return a public URL (http://www.example.com) as "master page":

|custom_documentation|


Registering the slots
.....................

In your extension, open ``EXT:your-ext/ext_localconf.php`` and add::

	/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
	$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');

	$signalSlotDispatcher->connect(
	    'Causal\\Sphinx\\Controller\\DocumentationController',
	    'afterInitializeReferences',
	    'Company\\MyExt\\Slots\\CustomDocumentation',
	    'postprocessReferences'
	);

	$signalSlotDispatcher->connect(
	    'Causal\\Sphinx\\Controller\\DocumentationController',
	    'renderUserDocumentation',
	    'Company\\MyExt\\Slots\\CustomDocumentation',
	    'render'
	);


Implementing the slots
......................

In your extension, create a file ``EXT:your-ext/Classes/Slots/CustomDocumentation.php``::

	<?php
	namespace Company\MyExt\Slots;

	class CustomDocumentation {

	    /**
	     * Registers the documentation.
	     *
	     * @param array &$references
	     * @return void
	     */
	    public function postprocessReferences(array &$references) {
	        $references['Some Category'] = array(
	            'USER:some-reference' => 'The Title',
	        );
	    }

	    /**
	     * Renders the documentation.
	     *
	     * @param string $identifier
	     * @param string $layout
	     * @param boolean $force
	     * @param string &$documentationUrl
	     * @return void
	     */
	    public function render($identifier, $layout, $force, &$documentationUrl) {
	        if ($identifier !== 'some-reference') {
	            return;
	        }

	        // TODO: render documentation and return an URL
	        //       (relative or absolute) to the master document
	        $documentationUrl = 'http://www.example.com';
	    }

	}

	?>
