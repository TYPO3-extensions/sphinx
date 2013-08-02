<?php
namespace Causal\Sphinx\Utility;

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

use \TYPO3\CMS\Core\Utility\GeneralUtility;

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
	 * @param string $sxwFilename
	 * @param string $outputDirectory
	 * @return void
	 * @throws \RuntimeException
	 */
	static public function convert($sxwFilename, $outputDirectory) {
		if (!\TYPO3\CMS\Core\Utility\CommandUtility::checkCommand('unzip')) {
			throw new \RuntimeException('Unzip cannot be executed.', 1375443057);
		}
		if (!function_exists('curl_init') || !($ch = curl_init())) {
			throw new \RuntimeException('Couldn\'t initialize cURL.', 1375438703);
		}

		$serviceUrl = 'http://docs.typo3.org/getthedocs/index.php';

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_USERAGENT, 'TYPO3 Sphinx');
		curl_setopt($ch, CURLOPT_URL, $serviceUrl);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		// same as <input type="file" name="manual">
		$post = array(
			'action' => 'convert',
			'manual' => '@' . $sxwFilename,
		);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		$response = curl_exec($ch);
		curl_close($ch);

		if (preg_match('/manual.sxw has been converted to reST and is available for download./', $response)
			&& preg_match('#(http://docs.typo3.org/getthedocs/files/\d+/Documentation.zip)#', $response, $matches)) {

			$documentationUrl = $matches[1];
			$zipFilename = PATH_site . 'typo3temp/documentation.zip';
			$zipContent = GeneralUtility::getUrl($documentationUrl);
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

?>