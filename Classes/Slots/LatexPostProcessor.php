<?php
namespace Causal\Sphinx\Slots;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Xavier Perseguers <xavier@causal.ch>
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
		$pattern = <<<TEX
\makeatother

\begin{document}
TEX;

		$texContents = preg_replace(
			'/' . preg_quote($pattern, '/') . '/',
			'\def\PYGZsq{\textquotesingle}' . LF . $pattern,
			$texContents
		);

		GeneralUtility::writeFile($texFileName, $texContents);
	}

}
