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

}

?>