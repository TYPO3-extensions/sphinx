<?php
namespace Causal\Sphinx\Utility;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * Rsync utility.
 *
 * @category    Utility
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class RsyncUtility
{

    /**
     * @var array
     */
    protected $fileExtensions = array();

    /**
     * Sets the file extensions.
     *
     * @param array $fileExtensions
     * @return array
     */
    public function setFileExtensions(array $fileExtensions)
    {
        $this->fileExtensions = array_unique(
            array_map(
                function ($e) {
                    if (($pos = strpos($e, '.')) !== false) $e = substr($e, $pos + 1);
                    return strtolower($e);
                },
                $fileExtensions
            )
        );
        sort($this->fileExtensions);
        return $this->fileExtensions;
    }

    /**
     * Returns the file extensions.
     *
     * @return array
     */
    public function getFileExtensions()
    {
        return $this->fileExtensions;
    }

    /**
     * Finds all files recursively in a path and returns them in an array.
     *
     * @param string $path
     * @param bool $useCache
     * @return array
     */
    public function getFilesRecursively($path, $useCache = false)
    {
        static $cachePaths = array();

        if ($useCache && isset($cachePaths[$path])) {
            return $cachePaths[$path];
        }

        $files = array();

        /** @var \RecursiveDirectoryIterator $iterator */
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path,
                \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            /** @var \splFileInfo $item */
            if (is_file($item)) {
                $fileName = $iterator->getSubPathname();
                $extension = '';
                if (($pos = strpos($fileName, '.')) !== false) {
                    $extension = strtolower(substr($fileName, $pos + 1));
                }
                if (empty($this->fileExtensions) || in_array($extension, $this->fileExtensions)) {
                    $md5 = md5_file($item);

                    $files[$fileName] = array(
                        'file' => $fileName,
                        'md5' => $md5,
                    );
                }
            }
        }

        // Sort files by name
        ksort($files);

        // Store in cache for quicker retrieval next time, if cache is used
        $cachePaths[$path] = $files;

        return $files;
    }

    /**
     * Returns an array of files present in $sourcePath but missing in $targetPath.
     *
     * @param string $sourcePath
     * @param string $targetPath
     * @param bool $useCache
     * @return array
     */
    public function getMissingFilesInTarget($sourcePath, $targetPath, $useCache = false)
    {
        $sourceFiles = $this->getFilesRecursively($sourcePath, $useCache);
        $targetFiles = $this->getFilesRecursively($targetPath, $useCache);

        $missingFiles = array_diff_key($sourceFiles, $targetFiles);
        return $missingFiles;
    }

    /**
     * Returns an array of files present in $targetPath but missing in $sourcePath.
     *
     * @param string $sourcePath
     * @param string $targetPath
     * @param bool $useCache
     * @return array
     */
    public function getExtraFilesInTarget($sourcePath, $targetPath, $useCache = false)
    {
        $sourceFiles = $this->getFilesRecursively($sourcePath, $useCache);
        $targetFiles = $this->getFilesRecursively($targetPath, $useCache);

        $extraFiles = array_diff_key($targetFiles, $sourceFiles);
        return $extraFiles;
    }

    /**
     * Returns an array of modified files in $targetPath.
     *
     * @param string $sourcePath
     * @param string $targetPath
     * @param bool $useCache
     * @return array
     */
    public function getModifiedFiles($sourcePath, $targetPath, $useCache = false)
    {
        $sourceFiles = $this->getFilesRecursively($sourcePath, $useCache);
        $targetFiles = $this->getFilesRecursively($targetPath, $useCache);

        $sameFiles = array_intersect(array_keys($sourceFiles), array_keys($targetFiles));
        $modifiedFiles = array();

        foreach ($sameFiles as $file) {
            if ($sourceFiles[$file]['md5'] !== $targetFiles[$file]['md5']) {
                $modifiedFiles[] = $file;
            }
        }

        return $modifiedFiles;
    }

    /**
     * Synchronizes $targetPath with files from $sourcePath, and takes care
     * of removing extra files from $targetPath.
     *
     * @param string $sourcePath
     * @param string $targetPath
     * @return void
     */
    public function synchronize($sourcePath, $targetPath)
    {
        $sourcePath = rtrim($sourcePath, '/') . '/';
        $targetPath = rtrim($targetPath, '/') . '/';

        $extraFiles = $this->getExtraFilesInTarget($sourcePath, $targetPath, true);
        $missingFiles = $this->getMissingFilesInTarget($sourcePath, $targetPath, true);
        $modifiedFile = $this->getModifiedFiles($sourcePath, $targetPath, true);

        // Remove extra files
        foreach ($extraFiles as $info) {
            $file = $info['file'];
            @unlink($targetPath . $file);
        }

        // Copy new files
        foreach ($missingFiles as $info) {
            $file = $info['file'];
            $path = PathUtility::dirname($targetPath . $file);
            if (!is_dir($path)) {
                GeneralUtility::mkdir($path, true);
            }
            copy($sourcePath . $file, $targetPath . $file);
        }

        // Overwrite modified files
        foreach ($modifiedFile as $file) {
            $path = PathUtility::dirname($targetPath . $file);
            if (!is_dir($path)) {
                GeneralUtility::mkdir($path, true);
            }
            copy($sourcePath . $file, $targetPath . $file);
        }
    }

}
