<?php
namespace Causal\Sphinx\ViewHelpers;

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

/**
 * Creates an objects.inv browser.
 *
 * @category    ViewHelpers
 * @package     tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class ObjectsInvBrowserViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Renders an objects.inv browser.
	 *
	 * @param string $reference Reference to the documentation project
	 * @param string $aceEditor Reference to the Ace editor
	 * @return string
	 */
	public function render($reference, $aceEditor) {
		if (substr($reference, 0, 4) !== 'EXT:') {
			return 'Sorry, the objects.inv browser currently only supports extension documentation';
		}
		$extensionKey = substr($reference, 4);

		$references = \Causal\Sphinx\Utility\GeneralUtility::getIntersphinxReferences($extensionKey);

		$out = array();
		//$out[] = '<h3 class="ui-widget-header">References</h3>';
		$out[] = '<div id="accordion-objectsinv" class="basic">';	// Start of accordion

		$lastMainChapter = '';
		foreach ($references as $chapter => $refs) {
			if (is_numeric($chapter)
				|| $chapter === 'genindex'
				|| $chapter === 'py-modindex'
				|| $chapter === 'search') {

				continue;
			}

			list($mainChapter, $_) = explode('/', $chapter, 2);
			if ($mainChapter !== $lastMainChapter) {
				if ($lastMainChapter !== '') {
					$out[] = '</div>';	// End of accordion content panel
				}
				$out[] = '<h3><a href="#">' . htmlspecialchars($mainChapter) . '</a></h3>';
				$out[] = '<div>';	// Start of accordion content panel
			}

			$out[] = '<h4>' . htmlspecialchars(substr($chapter, strlen($mainChapter))) . '</h4>';
			$out[] = '<ul>';
			foreach ($refs as $ref) {
				$restReference = ':ref:`' . $ref['name'] . '` ';
				$insertJS = $aceEditor . '.insert(\'' . str_replace(array('\'', '"'), array('\\\'', '\\"'), $restReference) . '\');';
				$insertJS .= $aceEditor . '.focus()';
				$out[] = '<li><a href="#" onclick="' . $insertJS . '">' . htmlspecialchars($ref['title']) . '</a></li>';
			}
			$out[] = '</ul>';

			$lastMainChapter = $mainChapter;
		}
		$out[] = '</div>';	// End of accordion content panel
		$out[] = '</div>';	// End of accordion

		$out[] = '<script type="text/javascript">';
		$out[] = <<<JS
$(document).ready(function() {
	$("#accordion-objectsinv").accordion({ heightStyle: "fill" });
});
JS;
		$out[] = '</script>';

		return implode(LF, $out);
	}

}

?>