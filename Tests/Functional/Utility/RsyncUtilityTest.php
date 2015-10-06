<?php
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

namespace Causal\Sphinx\Tests\Functional\Utility;

use Causal\Sphinx\Utility\RsyncUtility;
use Causal\Sphinx\Utility\MiscUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Testcase for class \Causal\Sphinx\Utility\RsyncUtility.
 */
class RsyncUtilityTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{

    /**
     * @var RsyncUtility
     */
    protected $rsyncUtility;

    /**
     * @var string
     */
    protected $fixturesPath;

    /**
     * @var string
     */
    protected $temporaryPath;

    public function setUp()
    {
        $this->rsyncUtility = new RsyncUtility();
        $this->fixturesPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('sphinx') .
            'Tests/Functional/Fixtures/';
        $this->temporaryPath = PATH_site . 'typo3temp/tx_sphinx_test_' . mt_rand(1, PHP_INT_MAX) . '/';
        GeneralUtility::mkdir($this->temporaryPath);
    }

    public function tearDown()
    {
        GeneralUtility::rmdir($this->temporaryPath, true);
        unset($this->temporaryPath);
        unset($this->fixturesPath);
        unset($this->rsyncUtility);
    }

    /**
     * @test
     */
    public function fileExtensionsAreMadeUnique()
    {
        $extensions = array('png', 'PNG', 'gif', 'jpg', '.jpg', '.GIF');

        $expected = array('gif', 'jpg', 'png');
        $actual = $this->rsyncUtility->setFileExtensions($extensions);

        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function canRetrieveAllFilesInDirectory()
    {
        $files = $this->rsyncUtility->getFilesRecursively($this->fixturesPath);

        $expected = array(
            array('file' => 'images/image1.png', 'md5' => 'edff2af19b9221bd7b9e4c9b2a7fa0e8'),
            array('file' => 'images/image2.png', 'md5' => '8a809ea6c905eafdced353bf88368158'),
            array('file' => 'sample.rst', 'md5' => 'a60319cd0397d5c9bcc0652f9a54c56c'),
        );

        $this->assertSame($expected, array_values($files));
    }

    /**
     * @test
     */
    public function canRetrieveImagesInDirectory()
    {
        $this->rsyncUtility->setFileExtensions(array('png'));
        $files = $this->rsyncUtility->getFilesRecursively($this->fixturesPath);

        $expected = array(
            array('file' => 'images/image1.png', 'md5' => 'edff2af19b9221bd7b9e4c9b2a7fa0e8'),
            array('file' => 'images/image2.png', 'md5' => '8a809ea6c905eafdced353bf88368158'),
        );

        $this->assertSame($expected, array_values($files));
    }

    /**
     * @test
     */
    public function noMissingFilesWhenComparingSameDirectory()
    {
        $files = $this->rsyncUtility->getMissingFilesInTarget($this->fixturesPath, $this->fixturesPath);
        $expected = array();

        $this->assertSame($expected, $files);
    }

    /**
     * @test
     */
    public function everyFileIsMissingInEmptyDirectory()
    {
        $files = $this->rsyncUtility->getMissingFilesInTarget($this->fixturesPath, $this->temporaryPath);
        $expected = array(
            array('file' => 'images/image1.png', 'md5' => 'edff2af19b9221bd7b9e4c9b2a7fa0e8'),
            array('file' => 'images/image2.png', 'md5' => '8a809ea6c905eafdced353bf88368158'),
            array('file' => 'sample.rst', 'md5' => 'a60319cd0397d5c9bcc0652f9a54c56c'),
        );

        $this->assertSame($expected, array_values($files));
    }

    /**
     * @test
     */
    public function extraFilesAreDetected()
    {
        MiscUtility::recursiveCopy($this->fixturesPath, $this->temporaryPath);

        $extraFiles = $this->createExtraFiles();
        $files = $this->rsyncUtility->getExtraFilesInTarget($this->fixturesPath, $this->temporaryPath);

        $this->assertSame($extraFiles, array_values($files));
    }

    /**
     * @test
     */
    public function modifiedFilesAreDetected() {
        MiscUtility::recursiveCopy($this->fixturesPath, $this->temporaryPath);

        // Change some files
        copy($this->temporaryPath . 'images/image1.png', $this->temporaryPath . 'images/image2.png');
        GeneralUtility::writeFile($this->temporaryPath . 'sample.rst', 'Modified content');

        $files = $this->rsyncUtility->getModifiedFiles($this->fixturesPath, $this->temporaryPath);
        $expected = array(
            'images/image2.png',
            'sample.rst',
        );

        $this->assertSame($expected, $files);
    }

    /**
     * @test
     */
    public function canSynchronizeDirectories()
    {
        MiscUtility::recursiveCopy($this->fixturesPath, $this->temporaryPath);
        $extraFiles = $this->createExtraFiles();

        // Change and remove some files
        copy($this->temporaryPath . 'images/image1.png', $this->temporaryPath . 'images/image2.png');
        @unlink($this->temporaryPath . 'images/image1.png');
        GeneralUtility::writeFile($this->temporaryPath . 'sample.rst', 'Modified content');

        $this->rsyncUtility->synchronize($this->fixturesPath, $this->temporaryPath);

        $fixtureFiles = $this->rsyncUtility->getFilesRecursively($this->fixturesPath);
        $temporaryFiles = $this->rsyncUtility->getFilesRecursively($this->temporaryPath);

        $this->assertSame($fixtureFiles, $temporaryFiles);
    }

    /**
     * Creates extra files in $this->temporaryPath.
     *
     * @return array
     */
    protected function createExtraFiles() {
        $extraFiles = array();

        for ($i = 0; $i < 3; $i++) {
            $content = 'This is extra file #' . $i;
            $extraFile = array(
                'file' => 'foo/bar-' . $i . '.txt',
                'md5' => md5($content),
            );

            GeneralUtility::mkdir($this->temporaryPath . dirname($extraFile['file']));
            GeneralUtility::writeFile($this->temporaryPath . $extraFile['file'], $content);

            $extraFiles[] = $extraFile;
        }

        return $extraFiles;
    }

}
