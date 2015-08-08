<?php
namespace Causal\Sphinx\Domain\Repository;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Causal\Sphinx\Utility\MiscUtility;

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
class DocumentationRepository implements \TYPO3\CMS\Core\SingletonInterface
{

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
    public function findManualsBySearchTerm($searchTerm)
    {
        $manuals = $this->getExtensionManuals();
        $sphinxManuals = array();
        $extensionKeys = array();

        foreach ($manuals as $extensionKey => $info) {
            if ($info['format'] == MiscUtility::DOCUMENTATION_TYPE_SPHINX) {
                $extensionKeys[] = $extensionKey;
                $sphinxManuals[] = array(
                    'extensionKey' => $extensionKey,
                    'locale' => 'default',
                    'remote' => 'https://docs.typo3.org/typo3cms/extensions/' . $extensionKey,
                );
                if (isset($info['localizations'])) {
                    foreach ($info['localizations'] as $locale) {
                        $sphinxManuals[] = array(
                            'extensionKey' => $extensionKey,
                            'locale' => $locale,
                            'remote' => 'https://docs.typo3.org/typo3cms/extensions/' . $extensionKey . '/' . str_replace('_', '-', strtolower($locale)),
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
                'id' => 'https://docs.typo3.org/typo3cms/extensions/' . $extensionKey,
                'label' => $extension['title'] . ' (' . $extensionKey . ')',
                'value' => $reference,
            );
            $manual = $manuals[$extensionKey];
            if (isset($manual['localizations'])) {
                foreach ($manual['localizations'] as $locale) {
                    $result[] = array(
                        'id' => 'https://docs.typo3.org/typo3cms/extensions/' . $extensionKey . '/' . str_replace('_', '-', strtolower($locale)),
                        'label' => $extension['title'] . ' (' . $extensionKey . '.' . $locale . ')',
                        'value' => $reference . '.' . $locale,
                    );
                }
            }
        }

        $documents = $this->getOfficialDocuments();
        foreach ($documents as $document) {
            if (stripos($document['shortcut'] . ' ' . $document['title'], $searchTerm) !== false) {
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
    protected function getExtensionManuals()
    {
        $json = MiscUtility::getUrlWithCache('https://docs.typo3.org/typo3cms/extensions/manuals.json');
        $manuals = json_decode($json, true);
        return is_array($manuals) ? $manuals : array();
    }

    /**
     * Returns the list of official documents.
     *
     * @return array
     * @see \TYPO3\CMS\Documentation\Service\DocumentationService::getOfficialDocuments()
     */
    public function getOfficialDocuments()
    {
        $json = MiscUtility::getUrlWithCache('https://docs.typo3.org/typo3cms/documents.json');
        $documents = json_decode($json, true);
        return is_array($documents) ? $documents : array();
    }

}
