<?php
namespace Causal\Sphinx\Domain\Model;

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
 * Domain model for an Extension.
 *
 * @category    Domain\Model
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Extension {

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
	public function getExtensionKey() {
		return $this->extensionKey;
	}

	/**
	 * Sets the extension key.
	 *
	 * @param string $extensionKey
	 * @return \Causal\Sphinx\Domain\Model\Extension This instance for method chaining
	 */
	public function setExtensionKey($extensionKey) {
		$this->extensionKey = $extensionKey;
		return $this;
	}

	/**
	 * Returns the title.
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets the title.
	 *
	 * @param string $title
	 * @return \Causal\Sphinx\Domain\Model\Extension This instance for method chaining
	 */
	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}

	/**
	 * Returns the icon.
	 *
	 * @return string
	 */
	public function getIcon() {
		return $this->icon;
	}

	/**
	 * Sets the icon.
	 *
	 * @param string $icon
	 * @return \Causal\Sphinx\Domain\Model\Extension This instance for method chaining
	 */
	public function setIcon($icon) {
		$this->icon = $icon;
		return $this;
	}

	/**
	 * Returns the install type.
	 *
	 * @return string
	 */
	public function getInstallType() {
		return $this->installType;
	}

	/**
	 * Sets the install type.
	 *
	 * @param string $installType
	 * @return \Causal\Sphinx\Domain\Model\Extension This instance for method chaining
	 */
	public function setInstallType($installType) {
		$this->installType = $installType;
		return $this;
	}

	/**
	 * Returns the description.
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Sets the description.
	 *
	 * @param string $description
	 * @return \Causal\Sphinx\Domain\Model\Extension This instance for method chaining
	 */
	public function setDescription($description) {
		$this->description = $description;
		return $this;
	}

	/**
	 * Returns the documentation package key.
	 *
	 * @return string
	 */
	public function getPackageKey() {
		return 'typo3cms.extensions.' . $this->extensionKey;
	}

}
