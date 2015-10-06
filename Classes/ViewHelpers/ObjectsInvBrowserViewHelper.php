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

namespace Causal\Sphinx\ViewHelpers;

/**
 * Creates an objects.inv browser.
 *
 * @category    ViewHelpers
 * @package     tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class ObjectsInvBrowserViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * Renders an objects.inv browser.
     *
     * @param string $id
     * @param string $reference Reference to the documentation project
     * @param string $cmEditor Reference to the CodeMirror editor
     * @param \Causal\Sphinx\Controller\RestEditorController $controller
     * @return string
     */
    public function render($id, $reference, $cmEditor, \Causal\Sphinx\Controller\RestEditorController $controller)
    {
        $out = array();
        $out[] = '<div id="' . $id . '" class="basic">';    // Start of accordion
        $out[] = $controller->accordionReferencesAction($reference, '', false, false);
        $out[] = '</div>';    // End of accordion

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
    ${cmEditor}.replaceSelection(str);
    ${cmEditor}.focus();

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
$(document).ready(function () {
    $('#$id').accordion({ heightStyle: 'fill' });
});
JS;
        $out[] = '</script>';

        return implode(LF, $out);
    }

}
