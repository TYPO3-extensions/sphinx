<?php
namespace Causal\Sphinx\Tests\Functional\Utility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013-2014 Xavier Perseguers <xavier@causal.ch>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use \Causal\Sphinx\Utility\GeneralUtility;

/**
 * Testcase for class \Causal\Sphinx\Utility\GeneralUtility.
 */
class GeneralUtilityTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {

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
		$references = GeneralUtility::getIntersphinxReferences('sphinx');
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
		GeneralUtility::generateDocumentation('sphinx', 'html');
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
		GeneralUtility::generateDocumentation('sphinx', 'json');
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
		GeneralUtility::generateDocumentation('sphinx', 'pdf');
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
		GeneralUtility::generateDocumentation('sphinx', 'html', FALSE, 'fr_FR');
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
		GeneralUtility::generateDocumentation('sphinx', 'json', FALSE, 'fr_FR');
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
		GeneralUtility::generateDocumentation('sphinx', 'pdf', FALSE, 'fr_FR');
		$this->assertTrue(is_file($masterFilename), 'Master file not found: ' . $masterFilename);
	}

}
