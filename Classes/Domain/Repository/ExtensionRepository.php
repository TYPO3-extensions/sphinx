<?php
namespace Causal\Sphinx\Domain\Repository;

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
 * Extension repository.
 *
 * @category    Domain\Repository
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class ExtensionRepository implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * Returns the list of loaded extensions with no documentation,
	 * sorted by extension title.
	 *
	 * @param string $allowedInstallTypes Defaults to 'S,G,L' to include System, Global and Local
	 * @return \Causal\Sphinx\Domain\Model\Extension[]
	 */
	public function findByHasNoDocumentation($allowedInstallTypes = 'S,G,L') {
		$loadedExtensionKeys = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getLoadedExtensionListArray();
		$extensions = array();
		$titles = array();

		foreach ($loadedExtensionKeys as $extensionKey) {
			$documentationType = \Causal\Sphinx\Utility\GeneralUtility::getDocumentationType($extensionKey);
			if ($documentationType === \Causal\Sphinx\Utility\GeneralUtility::DOCUMENTATION_TYPE_UNKNOWN
				&& \TYPO3\CMS\Core\Utility\GeneralUtility::inList($allowedInstallTypes, $GLOBALS['TYPO3_LOADED_EXT'][$extensionKey]['type'])) {

				$extension = $this->createExtensionObject($extensionKey);
				$extensions[$extensionKey] = $extension;
				$titles[$extensionKey] = strtolower($extension->getTitle());
			}
		}
		array_multisort($titles, SORT_ASC, $extensions);

		return $extensions;
	}

	/**
	 * Returns the list of loaded extensions with Sphinx documentation,
	 * sorted by extension title.
	 *
	 * @param string $allowedInstallTypes Defaults to 'S,G,L' to include System, Global and Local
	 * @return \Causal\Sphinx\Domain\Model\Extension[]
	 */
	public function findByHasSphinxDocumentation($allowedInstallTypes = 'S,G,L') {
		$loadedExtensionKeys = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getLoadedExtensionListArray();
		$extensions = array();
		$titles = array();

		foreach ($loadedExtensionKeys as $extensionKey) {
			$documentationType = \Causal\Sphinx\Utility\GeneralUtility::getDocumentationType($extensionKey);
			if (($documentationType === \Causal\Sphinx\Utility\GeneralUtility::DOCUMENTATION_TYPE_SPHINX
				|| $documentationType === \Causal\Sphinx\Utility\GeneralUtility::DOCUMENTATION_TYPE_README)
				&& \TYPO3\CMS\Core\Utility\GeneralUtility::inList($allowedInstallTypes, $GLOBALS['TYPO3_LOADED_EXT'][$extensionKey]['type'])) {

				$extension = $this->createExtensionObject($extensionKey);
				$extensions[$extensionKey] = $extension;
				$titles[$extensionKey] = strtolower($extension->getTitle());

				// Look for possible translations
				$supportedLocales = \Causal\Sphinx\Utility\SphinxBuilder::getSupportedLocales();
				foreach ($supportedLocales as $locale => $name) {
					$documentationType = \Causal\Sphinx\Utility\GeneralUtility::getLocalizedDocumentationType($extensionKey, $locale);
					if ($documentationType === \Causal\Sphinx\Utility\GeneralUtility::DOCUMENTATION_TYPE_SPHINX) {
						$localizationDirectories = \Causal\Sphinx\Utility\GeneralUtility::getLocalizationDirectories($extensionKey);
						$documentationKey = $extensionKey . '.' . $localizationDirectories[$locale]['locale'];

						$extension = $this->createExtensionObject($extensionKey);
						$extension->setExtensionKey($documentationKey);
						$extension->setTitle($extension->getTitle() . ' - ' . $name);

						$extensions[$documentationKey] = $extension;
						$titles[$documentationKey] = strtolower($extension->getTitle());
					}
				}
			}
		}
		array_multisort($titles, SORT_ASC, $extensions);

		return $extensions;
	}

	/**
	 * Returns the list of loaded extensions with OpenOffice documentation,
	 * sorted by extension title.
	 *
	 * @param string $allowedInstallTypes Defaults to 'S,G,L' to include System, Global and Local
	 * @return \Causal\Sphinx\Domain\Model\Extension[]
	 */
	public function findByHasOpenOffice($allowedInstallTypes = 'S,G,L') {
		$loadedExtensionKeys = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getLoadedExtensionListArray();
		$extensions = array();
		$titles = array();

		foreach ($loadedExtensionKeys as $extensionKey) {
			$documentationType = \Causal\Sphinx\Utility\GeneralUtility::getDocumentationType($extensionKey);
			if ($documentationType === \Causal\Sphinx\Utility\GeneralUtility::DOCUMENTATION_TYPE_OPENOFFICE
				&& \TYPO3\CMS\Core\Utility\GeneralUtility::inList($allowedInstallTypes, $GLOBALS['TYPO3_LOADED_EXT'][$extensionKey]['type'])) {

				$extension = $this->createExtensionObject($extensionKey);
				$extensions[$extensionKey] = $extension;
				$titles[$extensionKey] = strtolower($extension->getTitle());
			}
		}
		array_multisort($titles, SORT_ASC, $extensions);

		return $extensions;
	}

	/**
	 * Returns extensions matching a given list of extension keys and search terms.
	 *
	 * @param array $extensionKeys
	 * @param string $searchTerm
	 * @param integer $limit
	 * @return array
	 */
	public function findExtensionsBySearchTerm(array $extensionKeys, $searchTerm, $limit) {
		$extensionTable = 'tx_extensionmanager_domain_model_extension';
		$extensions = $this->getDatabaseConnection()->exec_SELECTgetRows(
			'DISTINCT extension_key, title',
			$extensionTable,
			$this->getSafeInClause('extension_key', array_map(function($e) { return "'$e'"; }, $extensionKeys)) .
			' AND ' . $this->getDatabaseConnection()->searchQuery(
				array($searchTerm),
				array('extension_key', 'title', 'description'),
				$extensionTable
			),
			'',
			'extension_key, last_updated',
			$limit,
			'extension_key'
		);

		// TYPO3 6.2 is shipping Sphinx-based manuals for system extensions but they are not published to
		// TER and as such cannot be cross-linked to easily
		$systemExtensions = $this->getSystemExtensionsWithSphinxDocumentation();
		foreach ($systemExtensions as $extension) {
			if (stripos($extension['extensionKey'], $searchTerm) !== FALSE
				|| stripos($extension['title'], $searchTerm) !== FALSE
				|| stripos($extension['description'], $searchTerm) !== FALSE) {

				$extensions[$extension['extensionKey']] = array(
					'extension_key' => $extension['extensionKey'],
					'title' => $extension['title']
				);
			}
		}

		if (count($extensions) > $limit) {
			$extensions = array_slice($extensions, 0, $limit);
		}

		return $extensions;
	}

	/**
	 * Returns an array of information regarding system extensions providing a Sphinx
	 * documentation (thus starting from TYPO3 6.2). This may be used in older versions
	 * of TYPO3 as well and even if the corresponding system extension is not loaded.
	 *
	 * @return array
	 */
	protected function getSystemExtensionsWithSphinxDocumentation() {
		$extensions = array();
		$extensions[] = array(
			'extensionKey' => 'css_styled_content',
			'title'        => 'CSS styled content',
			'description'  => 'Contains configuration for CSS content-rendering of the table "tt_content". This is meant as a modern substitute for the classic "content (default)" template which was based more on <font>-tags, while this is pure CSS. It is intended to work with all modern browsers (which excludes the NS4 series).',
		);
		$extensions[] = array(
			'extensionKey' => 'dbal',
			'title'        => 'Database Abstraction Layer',
			'description'  => 'A database abstraction layer implementation for TYPO3 4.6 based on ADOdb and offering a lot of other features.',
		);
		$extensions[] = array(
			'extensionKey' => 'felogin',
			'title'        => 'Frontend Login for Website Users',
			'description'  => 'A template-based plugin to log in Website Users in the Frontend',
		);
		$extensions[] = array(
			'extensionKey' => 'form',
			'title'        => 'Form',
			'description'  => 'Form Library, Plugin and Wizard',
		);
		$extensions[] = array(
			'extensionKey' => 'indexed_search',
			'title'        => 'Indexed Search Engine',
			'description'  => 'Indexed Search Engine for TYPO3 pages, PDF-files, Word-files, HTML and text files. Provides a backend module for statistics of the indexer and a frontend plugin. Documentation can be found in the extension "doc_indexed_search".',
		);
		$extensions[] = array(
			'extensionKey' => 'linkvalidator',
			'title'        => 'Link Validator',
			'description'  => 'Link Validator checks the links in your website for validity. It can validate all kinds of links: internal, external and file links. Scheduler is supported to run Link Validator via Cron including the option to send status mails, if broken links were detected.',
		);
		$extensions[] = array(
			'extensionKey' => 'openid',
			'title'        => 'OpenID authentication',
			'description'  => 'Adds OpenID authentication to TYPO3',
		);
		$extensions[] = array(
			'extensionKey' => 'recycler',
			'title'        => 'Recycler',
			'description'  => 'The recycler offers the possibility to restore deleted records or remove them from the database permanently. These actions can be applied to a single record, multiple records, and recursively to child records (ex. restoring a page can restore all content elements on that page). Filtering by page and by table provides a quick overview of deleted records before taking action on them.',
		);
		$extensions[] = array(
			'extensionKey' => 'rsaauth',
			'title'        => 'RSA authentication for TYPO3',
			'description'  => 'Contains a service to authenticate TYPO3 BE and FE users using private/public key encryption of passwords',
		);
		$extensions[] = array(
			'extensionKey' => 'rtehtmlarea',
			'title'        => 'htmlArea RTE',
			'description'  => 'Rich Text Editor.',
		);
		$extensions[] = array(
			'extensionKey' => 'saltedpasswords',
			'title'        => 'Salted user password hashes',
			'description'  => 'Uses a password hashing framework for storing passwords. Integrates into the system extension "felogin". Use SSL or rsaauth to secure datatransfer! Please read the manual first!',
		);
		$extensions[] = array(
			'extensionKey' => 'scheduler',
			'title'        => 'Scheduler',
			'description'  => 'The TYPO3 Scheduler let\'s you register tasks to happen at a specific time',
		);
		$extensions[] = array(
			'extensionKey' => 'sys_action',
			'title'        => 'User>Task Center, Actions',
			'description'  => 'Actions are \'programmed\' admin tasks which can be performed by selected regular users from the Task Center. An action could be creation of backend users, fixed SQL SELECT queries, listing of records, direct edit access to selected records etc.',
		);
		$extensions[] = array(
			'extensionKey' => 'taskcenter',
			'title'        => 'User>Task Center',
			'description'  => 'The Task Center is the framework for a host of other extensions, see below.',
		);
		$extensions[] = array(
			'extensionKey' => 'workspaces',
			'title'        => 'Workspaces Management',
			'description'  => 'Adds workspaces functionality with custom stages to TYPO3.',
		);

		return $extensions;
	}

	/**
	 * Creates an extension domain object from a given extension key.
	 *
	 * @param string $extensionKey
	 * @return \Causal\Sphinx\Domain\Model\Extension
	 */
	protected function createExtensionObject($extensionKey) {
		$info = $GLOBALS['TYPO3_LOADED_EXT'][$extensionKey];
		$metadata = \Causal\Sphinx\Utility\GeneralUtility::getExtensionMetaData($extensionKey);

		/** @var \Causal\Sphinx\Domain\Model\Extension $extension */
		$extension = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Causal\\Sphinx\\Domain\\Model\\Extension');
		$extension->setExtensionKey($extensionKey);
		$extension->setTitle($metadata['title']);
		$extension->setIcon($info['siteRelPath'] . $info['ext_icon']);
		$extension->setInstallType($info['type']);
		$extension->setDescription($metadata['description']);

		return $extension;
	}

	/**
	 * Returns the database connection.
	 *
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * Returns a safe IN clause because the number of values is limited by the DBMS.
	 * BEWARE: currently only works for numeric values!
	 *
	 * @param string $column
	 * @param array $values
	 * @return string|array
	 */
	protected function getSafeInClause($column, array $values) {
		$chunkSize = 1000;
		$clauses = array();

		while (count($values) > 0) {
			$chunk = array_slice($values, 0, $chunkSize);
			$clauses[] = $column . ' IN (' . implode(',', $chunk) . ')';
			$values = array_slice($values, count($chunk));
		}

		return '(' . implode(' OR ', $clauses) . ')';
	}

}
