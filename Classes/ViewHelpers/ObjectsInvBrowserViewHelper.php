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
	 * @param string $id
	 * @param string $reference Reference to the documentation project
	 * @param string $aceEditor Reference to the Ace editor
	 * @param \Causal\Sphinx\Controller\RestEditorController $controller
	 * @return string
	 */
	public function render($id, $reference, $aceEditor, \Causal\Sphinx\Controller\RestEditorController $controller) {
		if (substr($reference, 0, 4) !== 'EXT:') {
			return 'Sorry, the objects.inv browser currently only supports extension documentation';
		}

		$out = array();
		$out[] = '<div id="' . $id . '" class="basic">';	// Start of accordion
		$out[] = $controller->accordionReferencesAction($reference, '', FALSE, FALSE);
		$out[] = '</div>';	// End of accordion

		$uriBuilder = $this->controllerContext->getUriBuilder();
		$intersphinxAction = $uriBuilder->reset()->uriFor(
			'updateIntersphinx',
			array(
				'reference' => $reference,
				'prefix' => 'PREFIX',
				'remoteUrl' => 'URL',
			)
		);

		$out[] = '<script type="text/javascript">';
		$out[] = <<<JS
function EditorInsert(str, prefix, url) {
	${aceEditor}.insert(str);
	${aceEditor}.focus();

	if (prefix) {
		$.ajax({
			url: "${intersphinxAction}".replace(/PREFIX/, prefix).replace(/URL/, url)
		}).done(function(data) {
			if (data.status == 'success') {
				if (data.statusText) {
					CausalSphinx.Flashmessage.display(2, data.statusTitle, data.statusText, 2);
				}
			} else {
				CausalSphinx.Flashmessage.display(4, data.statusTitle, data.statusText);
			}
		});
	}
}
$(document).ready(function() {
	$('#$id').accordion({ heightStyle: 'fill' });
});
JS;
		$out[] = '</script>';

		return implode(LF, $out);
	}

}

?>