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

use \Causal\Sphinx\Utility\Configuration;

/**
 * Testcase for class \Causal\Sphinx\Utility\Configuration.
 */
class ConfigurationTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {

	/** @var string */
	protected $fixtureFilename;

	public function setUp() {
		$this->fixtureFilename = tempnam(PATH_typo3 . 'typo3temp', 'sphinx');
		$confpy = <<<PYTHON
templates_path = ['_templates']
source_suffix = 'rst'
master_doc = 'Index'
project = u'My Unit \'\\\' Test Project'
copyright = u'2013, Xavier Perseguers'
PYTHON;

		\TYPO3\CMS\Core\Utility\GeneralUtility::writeFile($this->fixtureFilename, $confpy);
	}

	public function tearDown() {
		@unlink($this->fixtureFilename);
	}

	/**
	 * @test
	 */
	public function canReadConfPy() {
		$configuration = Configuration::load($this->fixtureFilename);
		$this->assertTrue(is_array($configuration));
		$this->assertSame(5, count($configuration));
	}

	/**
	 * @test
	 */
	public function canDecodeEscapedCharacter() {
		$configuration = Configuration::load($this->fixtureFilename);
		$this->assertSame('My Unit \'\\\' Test Project', $configuration['project']);
	}

	/**
	 * @test
	 */
	public function doesNotDetectPackageT3sphinx() {
		$configuration = Configuration::load($this->fixtureFilename);
		$this->assertSame(FALSE, $configuration['t3sphinx']);
	}

	/**
	 * @test
	 */
	public function doesDetectPackageT3sphinx() {
		$confpy = file_get_contents($this->fixtureFilename);
		$confpy .= <<<PYTHON
if 1 and "TYPO3 specific":

    try:
        t3DocTeam
    except NameError:
        t3DocTeam = {}

    try:
        import t3sphinx
        html_theme_path.insert(0, t3sphinx.themes_dir)
        html_theme = 'typo3sphinx'
    except:
        html_theme = 'default'
PYTHON;
		\TYPO3\CMS\Core\Utility\GeneralUtility::writeFile($this->fixtureFilename, $confpy);

		$configuration = Configuration::load($this->fixtureFilename);
		$this->assertSame(TRUE, $configuration['t3sphinx']);
	}

}

?>