<?php
namespace Causal\Sphinx\Tests\Functional\Utility;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with TYPO3 source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Causal\Sphinx\Utility\MiscUtility;

/**
 * Testcase for class \Causal\Sphinx\Utility\MiscUtility.
 */
class MiscUtilityTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {

	public function setUp() {
		\TYPO3\CMS\Core\Utility\GeneralUtility::rmdir(PATH_site . 'typo3conf/Documentation/typo3cms.extensions.sphinx', TRUE);
	}

	/**
	 * @test
	 */
	public function canExtractIntersphinxReferencesForExtensionSphinx() {
		if (!\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('restdoc')) {
			$this->markTestIncomplete('This test requires extension "restdoc" to be loaded.');
		}
		$references = MiscUtility::getIntersphinxReferences('sphinx');
		$this->assertTrue(is_array($references));
		$this->assertTrue(isset($references['Index.htm']['start']));
	}

	/**
	 * @test
	 */
	public function canGenerateSphinxEnglishDocumentationAsHtml() {
		if (!\Causal\Sphinx\Utility\SphinxBuilder::isReady()) {
			$this->markTestIncomplete('This test requires a working Sphinx environment.');
		}
		$masterFilename = PATH_site . 'typo3conf/Documentation/typo3cms.extensions.sphinx/default/html/Index.html';
		$this->assertTrue(!is_file($masterFilename), 'Directory is not empty: ' . dirname($masterFilename));
		MiscUtility::generateDocumentation('sphinx', 'html');
		$this->assertTrue(is_file($masterFilename), 'Master file not found: ' . $masterFilename);
	}

	/**
	 * @test
	 */
	public function canGenerateSphinxEnglishDocumentationAsJson() {
		if (!\Causal\Sphinx\Utility\SphinxBuilder::isReady()) {
			$this->markTestIncomplete('This test requires a working Sphinx environment.');
		}
		$masterFilename = PATH_site . 'typo3conf/Documentation/typo3cms.extensions.sphinx/default/json/Index.fjson';
		$this->assertTrue(!is_file($masterFilename), 'Directory is not empty: ' . dirname($masterFilename));
		MiscUtility::generateDocumentation('sphinx', 'json');
		$this->assertTrue(is_file($masterFilename), 'Master file not found: ' . $masterFilename);
	}

	/**
	 * @test
	 */
	public function canGenerateSphinxEnglishDocumentationAsPdf() {
		if (!\Causal\Sphinx\Utility\SphinxBuilder::isReady()) {
			$this->markTestIncomplete('This test requires a working Sphinx environment.');
		}
		$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sphinx']);
		if ($configuration['pdf_builder'] !== 'pdflatex') {
			$this->markTestIncomplete('This test requires LaTeX to build PDF.');
		}
		$masterFilename = PATH_site . 'typo3conf/Documentation/typo3cms.extensions.sphinx/default/pdf/sphinx.pdf';
		$this->assertTrue(!is_file($masterFilename), 'Directory is not empty: ' . dirname($masterFilename));
		MiscUtility::generateDocumentation('sphinx', 'pdf');
		$this->assertTrue(is_file($masterFilename), 'Master file not found: ' . $masterFilename);
	}

	/**
	 * @test
	 */
	public function canGenerateSphinxFrenchDocumentationAsHtml() {
		if (!\Causal\Sphinx\Utility\SphinxBuilder::isReady()) {
			$this->markTestIncomplete('This test requires a working Sphinx environment.');
		}
		$masterFilename = PATH_site . 'typo3conf/Documentation/typo3cms.extensions.sphinx/fr_FR/html/Index.html';
		$this->assertTrue(!is_file($masterFilename), 'Directory is not empty: ' . dirname($masterFilename));
		MiscUtility::generateDocumentation('sphinx', 'html', FALSE, 'fr_FR');
		$this->assertTrue(is_file($masterFilename), 'Master file not found: ' . $masterFilename);
	}

	/**
	 * @test
	 */
	public function canGenerateSphinxFrenchDocumentationAsJson() {
		if (!\Causal\Sphinx\Utility\SphinxBuilder::isReady()) {
			$this->markTestIncomplete('This test requires a working Sphinx environment.');
		}
		$masterFilename = PATH_site . 'typo3conf/Documentation/typo3cms.extensions.sphinx/fr_FR/json/Index.fjson';
		$this->assertTrue(!is_file($masterFilename), 'Directory is not empty: ' . dirname($masterFilename));
		MiscUtility::generateDocumentation('sphinx', 'json', FALSE, 'fr_FR');
		$this->assertTrue(is_file($masterFilename), 'Master file not found: ' . $masterFilename);
	}

	/**
	 * @test
	 */
	public function canGenerateSphinxFrenchDocumentationAsPdf() {
		if (!\Causal\Sphinx\Utility\SphinxBuilder::isReady()) {
			$this->markTestIncomplete('This test requires a working Sphinx environment.');
		}
		$configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sphinx']);
		if ($configuration['pdf_builder'] !== 'pdflatex') {
			$this->markTestIncomplete('This test requires LaTeX to build PDF.');
		}
		$masterFilename = PATH_site . 'typo3conf/Documentation/typo3cms.extensions.sphinx/fr_FR/pdf/sphinx.pdf';
		$this->assertTrue(!is_file($masterFilename), 'Directory is not empty: ' . dirname($masterFilename));
		MiscUtility::generateDocumentation('sphinx', 'pdf', FALSE, 'fr_FR');
		$this->assertTrue(is_file($masterFilename), 'Master file not found: ' . $masterFilename);
	}

}
