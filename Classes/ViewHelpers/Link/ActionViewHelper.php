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

namespace Causal\Sphinx\ViewHelpers\Link;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use Causal\Sphinx\Utility\MiscUtility;

/**
 * A view helper for creating links to sphinx actions using the loading mask.
 *
 * = Examples =
 *
 * <code title="link to the show-action of the current controller">
 * <d:link.action action="show">action link</d:link.action>
 * </code>
 * <output>
 * <a href="#" onclick="top.TYPO3.Backend.ContentContainer.setUrl('index.php?id=123&tx_myextension_plugin[action]=show&tx_myextension_plugin[controller]=Standard&cHash=xyz')">action link</a>
 * (depending on the current page and your TS configuration)
 * </output>
 *
 * @TODO This class should be removed once TYPO3 6.2 is the only version supported as
 *       it is available from system extension EXT:documentation.
 */
class ActionViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Link\ActionViewHelper
{

    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerArgument('icon', 'string', 'Specifies the icon of a link');
    }

    /**
     * Renders the sphinx action link.
     *
     * @param string $action Target action
     * @param array $arguments Arguments
     * @param string $controller Target controller. If null current controllerName is used
     * @param string $extensionName Target Extension Name (without "tx_" prefix and no underscores). If null the current extension name is used
     * @param string $pluginName Target plugin. If empty, the current plugin name is used
     * @param integer $pageUid target page. See TypoLink destination
     * @param integer $pageType type of the target page. See typolink.parameter
     * @param boolean $noCache set this to disable caching for the target page. You should not need this.
     * @param boolean $noCacheHash set this to supress the cHash query parameter created by TypoLink. You should not need this.
     * @param string $section the anchor to be added to the URI
     * @param string $format The requested format, e.g. ".html
     * @param boolean $linkAccessRestrictedPages If set, links pointing to access restricted pages will still link to the page even though the page cannot be accessed.
     * @param array $additionalParams additional query parameters that won't be prefixed like $arguments (overrule $arguments)
     * @param boolean $absolute If set, the URI of the rendered link is absolute
     * @param boolean $addQueryString If set, the current query parameters will be kept in the URI
     * @param array $argumentsToBeExcludedFromQueryString arguments to be removed from the URI. Only active if $addQueryString = true
     * @param string $addQueryStringMethod Set which parameters will be kept. Only active if $addQueryString = TRUE
     * @param array $checks
     * @return string Rendered link
     */
    public function render($action = null, array $arguments = array(), $controller = null, $extensionName = null,
                           $pluginName = null, $pageUid = null, $pageType = 0, $noCache = false, $noCacheHash = false,
                           $section = '', $format = '', $linkAccessRestrictedPages = false, array $additionalParams = array(),
                           $absolute = false, $addQueryString = false, array $argumentsToBeExcludedFromQueryString = array(),
                           $addQueryStringMethod = null, array $checks = array())
    {

        $icon = $this->arguments['icon'];
        $this->tag->addAttribute('href', '#');
        if (!GeneralUtility::isFirstPartOfStr($action, 'javascript')) {
            $uriBuilder = $this->controllerContext->getUriBuilder();
            $uri = $uriBuilder
                ->reset()
                ->setTargetPageUid($pageUid)
                ->setTargetPageType($pageType)
                ->setNoCache($noCache)
                ->setUseCacheHash(!$noCacheHash)
                ->setSection($section)
                ->setFormat($format)
                ->setLinkAccessRestrictedPages($linkAccessRestrictedPages)
                ->setArguments($additionalParams)
                ->setCreateAbsoluteUri($absolute)
                ->setAddQueryString($addQueryString)
                ->setArgumentsToBeExcludedFromQueryString($argumentsToBeExcludedFromQueryString)
                ->uriFor($action, $arguments, $controller, $extensionName, $pluginName);

            $this->tag->addAttribute('onclick', 'top.TYPO3.Backend.ContentContainer.setUrl(\'' . $uri . '\')');
        } else {
            $this->tag->addAttribute('onclick', $action);
        }

        $content = $this->renderChildren();
        if ($icon) {
            /** @var \TYPO3\CMS\Core\Imaging\IconFactory $iconFactory */
            $iconFactory = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconFactory::class);
            $imgTag = $iconFactory->getIcon($icon, \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL)->render();
            $content = str_replace('<img src=', '<img title="' . htmlspecialchars(trim($content)) . '" src=', $imgTag);
        }

        foreach ($checks as $check => $value) {
            switch ($check) {
                case 'isWritable':
                    if (strpos($value, 'EXT:') === 0) {
                        $fileName = MiscUtility::extPath(substr($value, 4));
                    } else {
                        $fileName = GeneralUtility::getFileAbsFileName($value);
                    }
                    if (!is_writable($fileName)) {
                        $content .= sprintf(
                            ' <font style="color:#f00"><abbr title="%s">%s</abbr></font>',
                            htmlspecialchars($this->translate('dashboard.action.disabled.title')),
                            htmlspecialchars($this->translate('dashboard.action.disabled'))
                        );
                        $this->tag->addAttribute('onclick', 'return false');
                    }
                    break;
            }
        }

        $this->tag->setContent($content);
        $this->tag->forceClosingTag(true);
        return $this->tag->render();
    }

    /**
     * Translates a given id.
     *
     * @param string $id
     * @return string
     */
    protected function translate($id)
    {
        $request = $this->controllerContext->getRequest();
        $extensionName = $request->getControllerExtensionName();
        $value = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($id, $extensionName);
        return $value;
    }

}
