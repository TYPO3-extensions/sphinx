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
 * Domain model for a Project.
 *
 * @category    Domain\Model
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Project {

	/**
	 * @var string
	 * @notEmpty
	 */
	protected $documentationKey;

	/**
	 * @var string
	 * @notEmpty
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var string
	 * @notEmpty
	 */
	protected $group;

	/**
	 * @var string
	 * @notEmpty
	 */
	protected $directory;

	/**
	 * Returns the documentation key.
	 *
	 * @return string
	 */
	public function getDocumentationKey() {
		return $this->documentationKey;
	}

	/**
	 * Sets the documentation key.
	 *
	 * @param string $documentationKey
	 * @return \Causal\Sphinx\Domain\Model\Project This instance for method chaining
	 */
	public function setDocumentationKey($documentationKey) {
		$this->documentationKey = $documentationKey;
		return $this;
	}

	/**
	 * Returns the name.
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Sets the name.
	 *
	 * @param string $name
	 * @return \Causal\Sphinx\Domain\Model\Project This instance for method chaining
	 */
	public function setName($name) {
		$this->name = $name;
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
	 * @return \Causal\Sphinx\Domain\Model\Project This instance for method chaining
	 */
	public function setDescription($description) {
		$this->description = $description;
		return $this;
	}

	/**
	 * Returns the group.
	 *
	 * @return string
	 */
	public function getGroup() {
		return $this->group;
	}

	/**
	 * Sets the group
	 *
	 * @param string $group
	 * @return \Causal\Sphinx\Domain\Model\Project This instance for method chaining
	 */
	public function setGroup($group = '') {
		$this->group = $group;
		return $this;
	}

	/**
	 * Returns the directory.
	 *
	 * @return string
	 */
	public function getDirectory() {
		return $this->directory;
	}

	/**
	 * Sets the directory.
	 *
	 * @param string $directory
	 * @return \Causal\Sphinx\Domain\Model\Project This instance for method chaining
	 */
	public function setDirectory($directory) {
		$this->directory = $directory;
		return $this;
	}

}

?>