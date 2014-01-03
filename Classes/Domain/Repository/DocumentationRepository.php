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
 * Documentation repository.
 *
 * @category    Domain\Repository
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class DocumentationRepository implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var \Causal\Sphinx\Domain\Repository\ExtensionRepository
	 * @inject
	 */
	protected $extensionRepository;

	/**
	 * Returns a list of remote manuals given an extension query search term.
	 *
	 * @param string $searchTerm
	 * @return array()
	 */
	public function findManualsBySearchTerm($searchTerm) {
		$manuals = $this->getExtensionManuals();
		$sphinxManuals = array();
		$extensionKeys = array();

		foreach ($manuals as $extensionKey => $info) {
			if ($info['format'] == \Causal\Sphinx\Utility\GeneralUtility::DOCUMENTATION_TYPE_SPHINX) {
				$extensionKeys[] = $extensionKey;
				$sphinxManuals[] = array(
					'extensionKey' => $extensionKey,
					'locale' => 'default',
					'remote' => 'http://docs.typo3.org/typo3cms/extensions/' . $extensionKey,
				);
				if (isset($info['localizations'])) {
					foreach ($info['localizations'] as $locale) {
						$sphinxManuals[] = array(
							'extensionKey' => $extensionKey,
							'locale' => $locale,
							'remote' => 'http://docs.typo3.org/typo3cms/extensions/' . $extensionKey . '/' . str_replace('_', '-', strtolower($locale)),
						);
					}
				}
			}
		}

		$extensions = $this->extensionRepository->findExtensionsBySearchTerm($extensionKeys, $searchTerm, 15);

		$result = array();
		foreach ($extensions as $extension) {
			$extensionKey = $extension['extension_key'];
			$reference = 'EXT:' . $extensionKey;
			$result[] = array(
				'id' => 'http://docs.typo3.org/typo3cms/extensions/' . $extensionKey,
				'label' => $extension['title'] . ' (' . $extensionKey . ')',
				'value' => $reference,
			);
			$manual = $manuals[$extensionKey];
			if (isset($manual['localizations'])) {
				foreach ($manual['localizations'] as $locale) {
					$result[] = array(
						'id' => 'http://docs.typo3.org/typo3cms/extensions/' . $extensionKey . '/' . str_replace('_', '-', strtolower($locale)),
						'label' => $extension['title'] . ' (' . $extensionKey . '.' . $locale . ')',
						'value' => $reference . '.' . $locale,
					);
				}
			}
		}

		$documents = $this->getOfficialDocuments();
		foreach ($documents as $document) {
			if (stripos($document['shortcut'] . ' ' . $document['title'], $searchTerm) !== FALSE) {
				$result[] = array(
					'id' => $document['url'],
					'label' => $document['title'],
					'value' => $document['key'],
				);
			}
		}

		return $result;
	}

	/**
	 * Returns the list of extension manuals.
	 *
	 * @return array
	 */
	protected function getExtensionManuals() {
		$cacheFile = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName(
			'typo3temp/manuals.json'
		);
		if (!is_file($cacheFile) || $GLOBALS['EXEC_TIME'] - filemtime($cacheFile) > 86400) {
			$json = \Causal\Sphinx\Utility\GeneralUtility::getUrl('http://docs.typo3.org/typo3cms/extensions/manuals.json');
			if ($json) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::writeFile($cacheFile, $json);
			}
		}
		$manuals = array();
		if (is_file($cacheFile)) {
			$manuals = json_decode(file_get_contents($cacheFile), TRUE);
		}

		return is_array($manuals) ? $manuals : array();
	}

	/**
	 * Returns the list of official documents.
	 *
	 * @return array
	 */
	public function getOfficialDocuments() {
		// See \TYPO3\CMS\Documentation\Service\DocumentationService::getOfficialDocuments()
		$cacheFile = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName(
			'typo3temp/documents.json'
		);
		if (!is_file($cacheFile) || $GLOBALS['EXEC_TIME'] - filemtime($cacheFile) > 86400) {
			$json = \Causal\Sphinx\Utility\GeneralUtility::getUrl('http://docs.typo3.org/typo3cms/documents.json');
			if ($json) {
				\TYPO3\CMS\Core\Utility\GeneralUtility::writeFile($cacheFile, $json);
			}
		}
		$documents = array();
		if (is_file($cacheFile)) {
			$documents = json_decode(file_get_contents($cacheFile), TRUE);
		}

		return is_array($documents) ? $documents : array();
	}

}
