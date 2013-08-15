<?php
namespace Causal\Sphinx\Tests\Unit\Utility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Xavier Perseguers <xavier@causal.ch>
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

	/**
	 * @test
	 */
	public function canExtractMetadataForExtensionSphinx() {
		$metadata = GeneralUtility::getExtensionMetaData('sphinx');
		$this->assertTrue(is_array($metadata));
		$this->assertSame(24, count($metadata));
		$this->assertSame('sphinx', $metadata['extensionKey']);
		$this->assertSame('Xavier Perseguers (Causal)', $metadata['author']);
		$this->assertSame('Causal Sàrl', $metadata['author_company']);
		$this->assertSame('xavier@causal.ch', $metadata['author_email']);
		$this->assertSame('6.0.0-6.1.99', $metadata['constraints']['depends']['typo3']);
		$this->assertSame('1.1.1-dev', $metadata['release']);
		$this->assertSame('1.1', $metadata['version']);
	}

	/**
	 * @test
	 */
	public function extensionSphinxHasSphinxDocumentation() {
		$documentationType = GeneralUtility::getDocumentationType('sphinx');
		$this->assertSame(GeneralUtility::DOCUMENTATION_TYPE_SPHINX, $documentationType);
	}

	/**
	 * @test
	 */
	public function extensionAboutHasUnknownDocumentation() {
		$documentationType = GeneralUtility::getDocumentationType('about');
		$this->assertSame(GeneralUtility::DOCUMENTATION_TYPE_UNKNOWN, $documentationType);
	}

	/**
	 * @test
	 */
	public function extensionDocumentationHasREADMEDocumentation() {
		if (version_compare(TYPO3_version, '6.1.99', '<=')) {
			$this->markTestIncomplete(
				'This test can only run with TYPO3 6.2 LTS and above.'
			);
		}

		$documentationType = GeneralUtility::getDocumentationType('documentation');
		$this->assertSame(GeneralUtility::DOCUMENTATION_TYPE_README, $documentationType);
	}

	/**
	 * @test
	 */
	public function extensionSphinxHasFrenchDocumentation() {
		$localizationDirectories = GeneralUtility::getLocalizationDirectories('sphinx');
		$expected = array(
			'fr'    => array(
				'directory' => 'Documentation/Localization.fr_FR',
				'locale'    => 'fr_FR',
			),
			'fr_FR' => array(
				'directory' => 'Documentation/Localization.fr_FR',
				'locale'    => 'fr_FR',
			),
		);
		$this->assertSame($expected, $localizationDirectories);
	}

	/**
	 * @test
	 */
	public function extensionSphinxHasSphinxFrenchDocumentation() {
		$documentationType = GeneralUtility::getLocalizedDocumentationType('sphinx', 'fr_FR');
		$this->assertSame(GeneralUtility::DOCUMENTATION_TYPE_SPHINX, $documentationType);
	}

	/**
	 * @test
	 */
	public function extensionDocumentationHasNoFrenchDocumentation() {
		if (version_compare(TYPO3_version, '6.1.99', '<=')) {
			$this->markTestIncomplete(
				'This test can only run with TYPO3 6.2 LTS and above.'
			);
		}

		$documentationType = GeneralUtility::getLocalizedDocumentationType('documentation', 'fr_FR');
		$this->assertSame(GeneralUtility::DOCUMENTATION_TYPE_UNKNOWN, $documentationType);
	}

	/**
	 * @test
	 */
	public function canExtractEnglishDocumentationTitleForExtensionSphinx() {
		$projectTitle = GeneralUtility::getDocumentationProjectTitle('sphinx');
		$this->assertSame('Sphinx Python Documentation Generator and Viewer', $projectTitle);
	}

	/**
	 * @test
	 */
	public function canExtractFrenchDocumentationTitleForExtensionSphinx() {
		$projectTitle = GeneralUtility::getDocumentationProjectTitle('sphinx', 'fr_FR');
		$this->assertSame('Générateur et visionneuse de documentation Sphinx Python', $projectTitle);
	}

	/**
	 * @test
	 */
	public function cannotExtractGermanDocumentationTitleForExtensionSphinx() {
		$projectTitle = GeneralUtility::getDocumentationProjectTitle('sphinx', 'de_DE');
		$this->assertEmpty($projectTitle);
	}

}

?>