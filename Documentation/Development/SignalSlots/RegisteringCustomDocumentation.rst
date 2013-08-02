.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt
.. include:: Images.txt


Registering Custom Documentation
""""""""""""""""""""""""""""""""

The two slots :ref:`afterInitializeReferences <custom-documentation-afterInitializeReferences>` and :ref:`renderUserDocumentation <custom-documentation-renderUserDocumentation>` should be used to register and render your own documentation. Please see :ref:`sample code below <custom-documentation-sample>`.

Slot :ref:`retrieveRestFilename <custom-documentation-retrieveRestFilename>` should be used if you plan to edit source files using the integrated :ref:`ReStructuredText editor <sphinx-documentation-editor>`.


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
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

This slot is used to render your custom documentation and return the URL of the master page.

Your slot should implement a method of the form::

	public function render($identifier, $layout, $force, &$documentationUrl) {
	    // Custom code
	}


.. _custom-documentation-retrieveRestFilename:

Slot: retrieveRestFilename
^^^^^^^^^^^^^^^^^^^^^^^^^^

This slot is used to retrieve the ReStructuredText filename corresponding to a given document.

Your  slot should implement a method of the form::

	public function retrieveRestFilename($identifier, $document, &$filename) {
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
	$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
	    'TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher'
	);

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

	/*
	$signalSlotDispatcher->connect(
	    'Causal\\Sphinx\\Controller\\RestEditorController',
	    'retrieveRestFilename',
	    'Company\\MyExt\\Slots\\CustomDocumentation',
	    'retrieveRestFilename'
	);
	*/


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


Example implementation of the TODO
..................................

In example above the actual rendering of an arbitrary documentation is not shown. Here is an example of a possible implementation. We suppose that you have a TYPO3-based documentation project within ``fileadmin/demo-sphinx`` (e.g., generated with the :ref:`Sphinx Project Kickstarter <kickstart_sphinx_project>`), that is, a project where file ``conf.py`` is stored within a directory ``_make``::

	public function render($identifier, $layout, $force, &$documentationUrl) {
	    if ($identifier !== 'some-reference') {
	        return;
	    }

	    $basePath = 'fileadmin/demo-sphinx/';
	    $buildDirectory = '_make/build/';
	    $confFilename = '_make/conf.py';

	    try {
	        switch ($layout) {
	            case 'html':	// Static
	                $masterFile = '_make/build/html/Index.html';
	                if ($force || !is_file($basePath . $masterFile)) {
	                    \Causal\Sphinx\Utility\SphinxBuilder::buildHtml(
	                        PATH_site . $basePath,
	                        '.',
	                        $buildDirectory,
	                        $confFilename
	                    );
	                }
	                $documentationUrl = '../' . $basePath . $masterFile;
	                break;
	            case 'json':	// Interactive
	                $masterFile = '_make/build/html/Index.fjson';
	                if ($force || !is_file($basePath . $masterFile)) {
	                    \Causal\Sphinx\Utility\SphinxBuilder::buildJson(
	                        PATH_site . $basePath,
	                        '.',
	                        $buildDirectory,
	                        $confFilename
	                    );
	                }
	                $documentationUrl = '../' . $basePath . $masterFile;
	                break;
	            case 'pdf':
	            default:
	                throw new \RuntimeException(
	                    'Sorry! Layout ' . $layout . ' is not yet supported', 1371415095
	                );
	        }
	    } catch (\RuntimeException $e) {
	        $filename = 'typo3temp/tx_myext_' . $e->getCode() . '.log';
	        $content = $e->getMessage();
	        \TYPO3\CMS\Core\Utility\GeneralUtility::writeFile(PATH_site . $filename, $content);
	        $documentationUrl = '../' . $filename;
	    }
	}

Please see method ``\Causal\Sphinx\Utility\GeneralUtility::generateDocumentation()`` for further ideas.
