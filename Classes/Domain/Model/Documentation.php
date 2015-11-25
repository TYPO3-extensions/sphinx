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

namespace Causal\Sphinx\Domain\Model;

use Causal\Sphinx\Utility\MiscUtility;
use Causal\Restdoc\Utility\RestHelper;

/**
 * Domain model for a Documentation.
 *
 * @category    Domain\Model
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Documentation
{

    /**
     * @var \Causal\Restdoc\Reader\SphinxJson
     */
    protected $sphinxReader;

    /**
     * @var callback
     */
    protected $callbackLinks;

    /**
     * @var callback
     */
    protected $callbackImages;

    /**
     * Default constructor.
     *
     * @param \Causal\Restdoc\Reader\SphinxJson $sphinxReader
     */
    public function __construct(\Causal\Restdoc\Reader\SphinxJson $sphinxReader)
    {
        $this->sphinxReader = $sphinxReader;
    }

    /**
     * Sets the callback for processing the links.
     *
     * @param callback $callbackLinks Callback to generate Links in current context
     * @return \Causal\Sphinx\Domain\Model\Documentation
     */
    public function setCallbackLinks($callbackLinks)
    {
        $this->callbackLinks = $callbackLinks;
        return $this;
    }

    /**
     * Sets the callback for processing images.
     *
     * @param callback $callbackImages function to process images in current context
     * @return \Causal\Sphinx\Domain\Model\Documentation
     */
    public function setCallbackImages($callbackImages)
    {
        $this->callbackImages = $callbackImages;
        return $this;
    }

    /**
     * Returns the master table of contents.
     *
     * @return string
     */
    public function getMasterTableOfContents()
    {
        static $masterToc = null;
        if ($masterToc === null) {
            $masterToc = $this->sphinxReader->getMasterTableOfContents(false);
            $data = $masterToc ? RestHelper::getMenuData(RestHelper::xmlstr_to_array($masterToc)) : array();
            RestHelper::processMasterTableOfContents($data, null, $this->callbackLinks);
            $masterToc = $this->createMasterMenu($data);
        }
        return $masterToc;
    }

    /**
     * Returns the table of contents.
     *
     * @return string
     */
    public function getTableOfContents()
    {
        static $toc = null;
        if ($toc === null) {
            $toc = $this->sphinxReader->getTableOfContents($this->callbackLinks);
        }
        return $toc;
    }

    /**
     * Returns true if a table of contents exists.
     *
     * @return bool
     * @throws \RuntimeException
     */
    public function getHasTableOfContents()
    {
        // Must have an inner <ul> after the first one
        $tableOfContents = $this->getTableOfContents();
        if (strlen($tableOfContents) > 4) {
            return strpos($tableOfContents, '<ul>', 4) !== false;
        }
        return false;
    }

    /**
     * Returns the body.
     *
     * @return string
     * @see \tx_restdoc_pi1::generateIndex()
     */
    public function getBody()
    {
        static $body = null;
        if ($body === null) {
            if ($this->sphinxReader->getDocument() !== 'genindex/') {
                $body = $this->sphinxReader->getBody($this->callbackLinks, $this->callbackImages);
                $body = MiscUtility::postProcessPropertyTables($body);

                // Recreate list of labels for cross-referencing
                if (strpos($body, '<span id="labels-for-crossreferencing"></span>') !== false) {
                    $references = $this->sphinxReader->getReferences();
                    $body = MiscUtility::populateCrossReferencingLabels($body, $references, $this->callbackLinks);
                }
            } else {
                $linksCategories = array();
                $contentCategories = array();
                $indexEntries = $this->sphinxReader->getIndexEntries();

                foreach ($indexEntries as $indexGroup) {
                    $category = $indexGroup[0];
                    $anchor = 'tx-sphinx-index-' . htmlspecialchars($category);

                    $link = call_user_func($this->callbackLinks, 'genindex/');
                    $link .= '#' . $anchor;

                    $linksCategories[] = '<a href="' . $link . '"><strong>' . htmlspecialchars($category) . '</strong></a>';

                    $contentCategory = '<h2 id="' . $anchor . '">' . htmlspecialchars($category) . '</h2>' . LF;
                    $contentCategory .= '<div class="tx-sphinx-genindextable">' . LF;
                    $contentCategory .= RestHelper::getIndexDefinitionList($this->sphinxReader->getPath(), $indexGroup[1], $this->callbackLinks);
                    $contentCategory .= '</div>' . LF;

                    $contentCategories[] = $contentCategory;
                }

                $body = '<h1>Index</h1>' . LF;    // TODO: translate
                $body .= '<div class="tx-sphinx-genindex-jumpbox">' . implode(' | ', $linksCategories) . '</div>' . LF;
                $body .= implode(LF, $contentCategories);
            }
        }
        return $body;
    }

    /**
     * Returns the title, url and other global informations of the main document.
     *
     * @return array
     */
    public function getMainDocument()
    {
        static $data = null;
        if ($data === null) {
            $filename = $this->sphinxReader->getPath() . 'globalcontext.json';
            $content = file_get_contents($filename);
            $globalContext = json_decode($content, true);

            // Temporarily load the master document
            $filename = $this->sphinxReader->getPath() . $this->sphinxReader->getDefaultFile() . '.fjson';
            $content = file_get_contents($filename);
            $masterData = json_decode($content, true);

            $link = call_user_func($this->callbackLinks, $this->sphinxReader->getDefaultFile() . '/');
            $data = array(
                'title' => $masterData['title'],
                'version' => $globalContext['version'],
                'release' => $globalContext['release'],
                'copyright' => $globalContext['copyright'],
                'url' => $link,
                'sphinx_version' => $globalContext['sphinx_version'],
            );
        }
        return $data;
    }

    /**
     * Returns the title and url of the previous document.
     *
     * @return array|null
     */
    public function getPreviousDocument()
    {
        static $data = null;
        if ($data === null) {
            $previousDocument = $this->sphinxReader->getPreviousDocument();
            if ($previousDocument !== null) {
                $absolute = RestHelper::relativeToAbsolute($this->sphinxReader->getPath() . $this->sphinxReader->getDocument(), '../' . $previousDocument['link']);
                $link = call_user_func($this->callbackLinks, substr($absolute, strlen($this->sphinxReader->getPath())));

                $data = array(
                    'title' => $previousDocument['title'],
                    'url' => $link,
                );
            }
        }
        return $data;
    }

    /**
     * Returns the title and url of the next document.
     *
     * @return array|null
     */
    public function getNextDocument()
    {
        static $data = null;
        if ($data === null) {
            $nextDocument = $this->sphinxReader->getNextDocument();
            if ($nextDocument !== null) {
                if ($this->sphinxReader->getDocument() === $this->sphinxReader->getDefaultFile() . '/' && substr($nextDocument['link'], 0, 3) !== '../') {
                    $nextDocumentPath = $this->sphinxReader->getPath();
                } else {
                    $nextDocumentPath = $this->sphinxReader->getPath() . $this->sphinxReader->getDocument();
                }
                $absolute = RestHelper::relativeToAbsolute($nextDocumentPath, '../' . $nextDocument['link']);
                $link = call_user_func($this->callbackLinks, substr($absolute, strlen($this->sphinxReader->getPath())));

                $data = array(
                    'title' => $nextDocument['title'],
                    'url' => $link,
                );
            }
        }
        return $data;
    }

    /**
     * Returns the title and url of the parent document.
     *
     * @return array|null
     */
    public function getParentDocument()
    {
        static $data = null;
        if ($data === null) {
            $parentDocuments = $this->sphinxReader->getParentDocuments();
            if (empty($parentDocuments)) {
                if ($this->sphinxReader->getDocument() !== $this->sphinxReader->getDefaultFile() . '/') {
                    $data = $this->getMainDocument();
                }
            } else {
                $parentDocument = end($parentDocuments);
                $parentDocumentPath = $this->sphinxReader->getPath() . $this->sphinxReader->getDocument();

                $absolute = RestHelper::relativeToAbsolute($parentDocumentPath, '../' . $parentDocument['link']);
                $link = call_user_func($this->callbackLinks, substr($absolute, strlen($this->sphinxReader->getPath())));

                $data = array(
                    'title' => $parentDocument['title'],
                    'url' => $link,
                );
            }
        }
        return $data;
    }

    /**
     * Returns the title and url of the general index.
     *
     * @return array
     */
    public function getGeneralIndex()
    {
        return array(
            'title' => 'General Index',    // TODO: translate!
            'url' => call_user_func($this->callbackLinks, 'genindex/'),
        );
    }

    /**
     * Magic getter method for the Sphinx reader.
     *
     * @param string $methodName
     * @param array $arguments
     * @return mixed|null
     */
    public function __call($methodName, array $arguments)
    {
        if (is_callable(array($this->sphinxReader, $methodName))) {
            return call_user_func(array($this->sphinxReader, $methodName), $arguments);
        }
        return null;
    }

    /**
     * Creates a master menu compatible with the interactive design.
     *
     * @param array $data
     * @param integer $level
     * @return array
     */
    protected function createMasterMenu(array $data, $level = 1)
    {
        $menu = array();
        if ($level == 1) {
            $menu[] = '<ul class="current">';
            $wrapTitle = '%s';
        } else {
            $menu[] = '<ul>';
            $wrapTitle = '%s';
        }

        foreach ($data as $menuEntry) {
            if (isset($menuEntry['ITEM_STATE']) && in_array($menuEntry['ITEM_STATE'], array('ACT', 'CUR'))) {
                $currentClass = ' current';
            } else {
                $currentClass = '';
            }

            $menu[] = '<li class="toctree-l' . $level . $currentClass . '">';
            $menu[] = '<a href="' . str_replace('&', '&amp;', $menuEntry['_OVERRIDE_HREF']) . '" class="nav-aside-lvl' . $level . $currentClass . '">' .
                sprintf($wrapTitle, htmlspecialchars($menuEntry['title'])) .
                '</a>';

            $generateSubMenu = $level == 1;
            $generateSubMenu &= isset($menuEntry['_SUB_MENU']);

            if ($generateSubMenu) {
                $menu[] = $this->createMasterMenu($menuEntry['_SUB_MENU'], $level + 1);
            }
            $menu[] = '</li>';
        }

        $menu[] = '</ul>';

        return implode('', $menu);
    }

}
