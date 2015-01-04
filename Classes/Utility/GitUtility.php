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

use TYPO3\CMS\Core\Utility\CommandUtility;
use Causal\Sphinx\Utility\MiscUtility;

/**
 * Git utility.
 *
 * @category    Utility
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class GitUtility {

	/**
	 * Returns TRUE if git is available, otherwise FALSE.
	 *
	 * @return bool
	 */
	static public function isAvailable() {
		return CommandUtility::getCommand('git') !== '';
	}

	/**
	 * Returns TRUE if $uri is (well, looks like) a valid Git URI.
	 *
	 * @param string $uri
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	static public function isValidRepository($uri) {
		if (substr($uri, 0, 6) === 'git://') {
			$isValid = MiscUtility::checkUrl(str_replace('git://', 'https://', $uri))
				|| MiscUtility::checkUrl(str_replace('git://', 'http://', $uri));
		} else {
			$isValid = MiscUtility::checkUrl($uri);
		}
		return $isValid;
	}

	/**
	 * Clones a Git repository.
	 *
	 * @param string $uri
	 * @param string $contextPath Base path
	 * @param string $targetDirectory Optional alternate target directory
	 * @param NULL|array $output
	 * @return bool
	 */
	static public function cloneRepository($uri, $contextPath, $targetDirectory = '', &$output = NULL) {
		// -C flag does not work under Windows, thus we do a "cd" and then a "git clone"
		$cmd = 'cd ' . escapeshellarg($contextPath) . ' && ' .
			static::getGitCommand() . ' clone ' . $uri;
		if (!empty($targetDirectory)) {
			$cmd .= ' ' . escapeshellarg($targetDirectory);
		}
		CommandUtility::exec($cmd, $output, $returnValue);
		return $returnValue == 0;
	}

	/**
	 * Checks status of a Git repository.
	 *
	 * @param string $contextPath Base path
	 * @param NULL|array $output
	 * @return bool TRUE if status succeeded, otherwise FALSE
	 */
	static public function status($contextPath, &$output = NULL) {
		$cmd = 'cd ' . escapeshellarg($contextPath) . ' && ' .
			static::getGitCommand() . ' status';
		CommandUtility::exec($cmd, $output, $returnValue);
		return $returnValue == 0;
	}

	/**
	 * Adds a file.
	 *
	 * @param string $contextPath Base path for relative path/filename
	 * @param string $fileName Relative filename
	 * @param NULL|array $output
	 * @return bool TRUE if add succeeded, otherwise FALSE
	 */
	static public function add($contextPath, $fileName, &$output = NULL) {
		$cmd = 'cd ' . escapeshellarg($contextPath) . ' && ' .
			static::getGitCommand() . ' add ' . escapeshellarg($fileName);
		CommandUtility::exec($cmd, $output, $returnValue);
		return $returnValue == 0;
	}

	/**
	 * Moves a file.
	 *
	 * @param string $contextPath Base path for relative path/filename (NO trailing directory separator)
	 * @param string $sourceFileName Relative source path/filename
	 * @param string $targetFileName Relative target path/filename
	 * @param NULL|array $output
	 * @return bool TRUE if move succeeded, otherwise FALSE
	 */
	static public function move($contextPath, $sourceFileName, $targetFileName, &$output = NULL) {
		if (static::isFileTracked($contextPath, $sourceFileName)) {
			$cmd = 'cd ' . escapeshellarg($contextPath) . ' && ' .
				static::getGitCommand() . ' mv ' . escapeshellarg($sourceFileName) . ' ' . escapeshellarg($targetFileName);
			CommandUtility::exec($cmd, $output, $returnValue);
			$success = $returnValue == 0;
		} else {
			$success = rename($contextPath . DIRECTORY_SEPARATOR . $sourceFileName, $contextPath . DIRECTORY_SEPARATOR . $targetFileName);
		}
		return $success;
	}

	/**
	 * Removes a file.
	 *
	 * @param string $contextPath Base path for relative path/filename (NO trailing directory separator)
	 * @param string $fileName Relative filename
	 * @param NULL|array $output
	 * @return bool TRUE if remove succeeded, otherwise FALSE
	 */
	static public function remove($contextPath, $fileName, &$output = NULL) {
		if (static::isFileTracked($contextPath, $fileName)) {
			$cmd = 'cd ' . escapeshellarg($contextPath) . ' && ' .
				static::getGitCommand() . ' rm -f ' . escapeshellarg($fileName);
			CommandUtility::exec($cmd, $output, $returnValue);
			$success = $returnValue == 0;
		} else {
			$success = @unlink($contextPath . DIRECTORY_SEPARATOR . $fileName);
		}
		return $success;
	}

	/**
	 * Returns TRUE if a given file is tracked in a Git repository.
	 *
	 * @param string $contextPath Base path for relative filename
	 * @param string $fileName Relative filename
	 * @param NULL|array $output
	 * @return bool TRUE if file is tracked, otherwise FALSE
	 */
	static public function isFileTracked($contextPath, $fileName, &$output = NULL) {
		$cmd = 'cd ' . escapeshellarg($contextPath) . ' && ' .
			static::getGitCommand() . ' ls-files ' . escapeshellarg($fileName) . ' --error-unmatch';
		CommandUtility::exec($cmd, $output, $returnValue);
		return $returnValue == 0;
	}

	/**
	 * Returns the escaped Git shell command.
	 *
	 * @return string
	 */
	static protected function getGitCommand() {
		$cmd = CommandUtility::getCommand('git');
		return escapeshellarg($cmd);
	}

}
