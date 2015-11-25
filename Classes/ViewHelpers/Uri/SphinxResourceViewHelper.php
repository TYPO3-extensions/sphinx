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

namespace Causal\Sphinx\ViewHelpers\Uri;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;

/**
 * A view helper for creating URIs to Sphinx resources
 *
 * @category    ViewHelpers
 * @package     tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class SphinxResourceViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper implements CompilableInterface
{

    /**
     * Render the URI to the resource. The filename is used from child content.
     *
     * @param string $path The path and filename of the resource (relative to Public resource directory of the extension).
     * @param bool $absolute If set, an absolute URI is rendered
     * @return string The URI to the resource
     * @api
     */
    public function render($path, $absolute = false)
    {
        return static::renderStatic(
            array(
                'path' => $path,
                'absolute' => $absolute
            ),
            $this->buildRenderChildrenClosure(),
            $this->renderingContext
        );
    }

    /**
     * @param array $arguments
     * @param callable $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $path = $arguments['path'];
        $absolute = $arguments['absolute'];

        $uri = 'uploads/tx_sphinx/' . $path;
        $uri = GeneralUtility::getFileAbsFileName($uri);
        $uri = \TYPO3\CMS\Core\Utility\PathUtility::stripPathSitePrefix($uri);
        if (TYPO3_MODE === 'BE' && $absolute === false && $uri !== false) {
            $uri = '../' . $uri;
        }
        if ($absolute === true) {
            $uri = $renderingContext->getControllerContext()->getRequest()->getBaseUri() . $uri;
        }
        return $uri;
    }

}
