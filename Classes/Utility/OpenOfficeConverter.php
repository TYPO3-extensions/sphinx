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

/**
 * OpenOffice converter.
 *
 * @category    Utility
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class OpenOfficeConverter {

	/**
	 * Converts an OpenOffice document to Sphinx using the online
	 * converter on http://docs.typo3.org/getthedocs/service-convert.html.
	 *
	 * @param string $sxwFilename Absolute path to the OpenOffice document (*.sxw)
	 * @param string $outputDirectory Absolute path to the directory where the Sphinx project should be created
	 * @return void
	 * @throws \RuntimeException
	 */
	static public function convert($sxwFilename, $outputDirectory) {
		if (!\TYPO3\CMS\Core\Utility\CommandUtility::checkCommand('unzip')) {
			throw new \RuntimeException('Unzip cannot be executed. Hint: You probably should double-check '.
				'$TYPO3_CONF_VARS[\'SYS\'][\'binPath\'] and/or $TYPO3_CONF_VARS[\'SYS\'][\'binSetup\'].', 1375443057);
		}
		if (!function_exists('curl_init') || !($ch = curl_init())) {
			throw new \RuntimeException('Couldn\'t initialize cURL. Please load PHP extension curl.', 1375438703);
		}

		$serviceUrl = 'http://docs.typo3.org/getthedocs/index.php';

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_USERAGENT, 'TYPO3 Sphinx');
		curl_setopt($ch, CURLOPT_URL, $serviceUrl);
		curl_setopt($ch, CURLOPT_POST, TRUE);

		// PHP 5.5 introduced a CurlFile object that deprecates the old @filename syntax
		// See: https://wiki.php.net/rfc/curl-file-upload
		if (function_exists('curl_file_create')) {
			$value = curl_file_create($sxwFilename);
		} else {
			$value = '@' . $sxwFilename;
		}
		$post = array(
			'action' => 'convert',
			// same as <input type="file" name="manual">
			'manual' => $value,
		);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		$response = curl_exec($ch);
		curl_close($ch);

		if (preg_match('/manual.sxw has been converted to reST and is available for download./', $response)
			&& preg_match('#(http://docs.typo3.org/getthedocs/files/\d+/Documentation.zip)#', $response, $matches)) {

			$documentationUrl = $matches[1];
			$zipFilename = GeneralUtility::getFileAbsFileName('typo3temp/documentation.zip');
			$zipContent = MiscUtility::getUrl($documentationUrl);
			if ($zipContent && GeneralUtility::writeFile($zipFilename, $zipContent)) {
				GeneralUtility::rmdir($outputDirectory, TRUE);
				GeneralUtility::mkdir_deep($outputDirectory . DIRECTORY_SEPARATOR);
				\Causal\Sphinx\Utility\Setup::unarchive($zipFilename, $outputDirectory, 'Documentation');
			} else {
				throw new \RuntimeException('Could not download archive ' . $documentationUrl, 1375443637);
			}
		} else {
			throw new \RuntimeException('Conversion failed for ' . $sxwFilename, 1375443657);
		}
	}

}
