<?php
namespace Causal\Sphinx\Slots;

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
 * LaTeX post-processor for EXT:sphinx.
 *
 * @category    Slots
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class LatexPostProcessor {

	/**
	 * Post-process a generated LaTeX file.
	 *
	 * @param string $texFileName
	 * @return void
	 */
	public function postprocess($texFileName) {
		$texContents = file_get_contents($texFileName);

		// Fix single quotes (@see http://forge.typo3.org/issues/53408)
		$pattern = '\\makeatother' . PHP_EOL . PHP_EOL . '\\begin{document}';
		$texContents = preg_replace(
			'/' . preg_quote($pattern, '/') . '/i',
			'\\def\\PYGZsq{\\textquotesingle}' . PHP_EOL . $pattern,
			$texContents
		);

		GeneralUtility::writeFile($texFileName, $texContents);
	}

}
