<?php
namespace Causal\Sphinx\Domain\Model;

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
	 */
	protected $uid;

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
	protected $language;

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
	 * Default constructor.
	 *
	 * @param string $documentationKey
	 */
	public function __construct($documentationKey) {
		$this->uid = $documentationKey;
		$this->documentationKey = $documentationKey;
	}

	/**
	 * Returns the (original) documentation key.
	 *
	 * @return string
	 */
	public function getUid() {
		return $this->uid;
	}

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
	 * Returns the reference.
	 *
	 * @return string
	 */
	public function getReference() {
		return 'USER:' . $this->documentationKey;
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
	 * Returns the language.
	 *
	 * @return string
	 */
	public function getLanguage() {
		return $this->language;
	}

	/**
	 * Sets the language.
	 *
	 * @param string $language
	 * @return \Causal\Sphinx\Domain\Model\Project This instance for method chaining
	 */
	public function setLanguage($language) {
		$this->language = $language;
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
