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

/**
 * Domain model for an Extension.
 *
 * @category    Domain\Model
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Extension
{

    /**
     * @var string
     */
    protected $extensionKey;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $icon;

    /**
     * @var string
     */
    protected $installType;

    /**
     * @var string
     */
    protected $description;

    /**
     * Returns the extension key.
     *
     * @return string
     */
    public function getExtensionKey()
    {
        return $this->extensionKey;
    }

    /**
     * Sets the extension key.
     *
     * @param string $extensionKey
     * @return \Causal\Sphinx\Domain\Model\Extension This instance for method chaining
     */
    public function setExtensionKey($extensionKey)
    {
        $this->extensionKey = $extensionKey;
        return $this;
    }

    /**
     * Returns the title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title.
     *
     * @param string $title
     * @return \Causal\Sphinx\Domain\Model\Extension This instance for method chaining
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Returns the icon.
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Sets the icon.
     *
     * @param string $icon
     * @return \Causal\Sphinx\Domain\Model\Extension This instance for method chaining
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Returns the install type.
     *
     * @return string
     */
    public function getInstallType()
    {
        return $this->installType;
    }

    /**
     * Sets the install type.
     *
     * @param string $installType
     * @return \Causal\Sphinx\Domain\Model\Extension This instance for method chaining
     */
    public function setInstallType($installType)
    {
        $this->installType = $installType;
        return $this;
    }

    /**
     * Returns the description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the description.
     *
     * @param string $description
     * @return \Causal\Sphinx\Domain\Model\Extension This instance for method chaining
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Returns the documentation package key.
     *
     * @return string
     */
    public function getPackageKey()
    {
        return 'typo3cms.extensions.' . $this->extensionKey;
    }

}
