<?php
namespace Causal\Sphinx;

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

$BACK_PATH = $GLOBALS['BACK_PATH'] . TYPO3_mainDir;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Causal\Sphinx\Utility\Setup;

/**
 * Class to be used to initialize the Sphinx Python Documentation Generator locally.
 *
 * @category    Extension Manager
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   2013-2016 Causal Sàrl
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class ext_update extends \TYPO3\CMS\Backend\Module\BaseScriptClass
{

    /** @var string */
    protected $extKey = 'sphinx';

    /** @var array */
    protected $configuration;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        $this->configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
    }

    /**
     * Checks whether the "UPDATE!" menu item should be
     * shown.
     *
     * @return boolean
     */
    public function access()
    {
        return true;
    }

    /**
     * Main method that is called whenever UPDATE! menu
     * was clicked.
     *
     * @return string HTML to display
     */
    public function main()
    {
        $out = array();

        $errors = Setup::createLibraryDirectories();
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $out[] = $this->formatError($error);
            }
            return implode(LF, $out);
        }

        // Sphinx actually relies by default on Jinja2 as templating engine and Jinja2 requires Python 2.6
        // We don't check this as a hard requirement but will issue a warning
        $pythonVersion = Setup::getPythonVersion();
        if (version_compare($pythonVersion, '2.6', '<')) {
            $out[] = $this->formatWarning('The default templating language in Sphinx is Jinja. Jinja requires at least ' .
                'Python 2.6 but you are using ' . $pythonVersion . '. As such it is very likely that you will not be ' .
                'able to actually render Sphinx projects.');
        }

        // Fetch the list of official versions of Sphinx
        $availableVersions = Setup::getSphinxAvailableVersions();
        // Load the list of locally available versions of Sphinx
        $localVersions = Setup::getSphinxLocalVersions();

        if (count($availableVersions) == 0) {
            $message = <<<HTML
Could not find any version of Sphinx:<br /><br />
<ul>
	<li>Are you currently offline?</li>
	<li>Does PHP have <a href="http://wiki.typo3.org/Exception/CMS/1318283565">proper OpenSSL support</a>?</li>
HTML;

            if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['curlUse'] == '1') {
                $message .= '<li>You have $GLOBALS[\'TYPO3_CONF_VARS\'][\'SYS\'][\'curlUse\'] == \'1\', ' .
                    'your OpenSSL configuration might be broken</li>';
            }

            $message .= '</ul>';

            $out[] = $this->formatWarning($message, false);
        }

        // Handle form operation, if needed
        $operation = GeneralUtility::_GP('operation');
        if ($operation) {
            list($action, $version) = explode('-', $operation, 2);

            if (isset($availableVersions[$version])) {
                $messages = array();

                switch ($action) {
                    case 'DOWNLOAD':
                        $this->downloadSphinx($availableVersions[$version], $messages);
                        break;
                    case 'BUILD':
                        $this->buildSphinx($availableVersions[$version], $messages);
                        break;
                    case 'IMPORT':
                        if ($this->downloadSphinx($availableVersions[$version], $messages)) {
                            $this->buildSphinx($availableVersions[$version], $messages);
                        }
                        break;
                    case 'REMOVE':
                        $this->removeSphinx($availableVersions[$version], $messages);
                        break;
                }

                foreach ($messages as $message) {
                    switch (true) {
                        case GeneralUtility::isFirstPartOfStr($message, '[OK] '):
                            $out[] = $this->formatOk(substr($message, 5));
                            break;
                        case GeneralUtility::isFirstPartOfStr($message, '[INFO] '):
                            $out[] = $this->formatInformation(substr($message, 7));
                            break;
                        case GeneralUtility::isFirstPartOfStr($message, '[WARNING] '):
                            $out[] = $this->formatWarning(substr($message, 10));
                            break;
                        case GeneralUtility::isFirstPartOfStr($message, '[ERROR] '):
                            $out[] = $this->formatError(substr($message, 8));
                            break;
                        default:
                            $out[] = $message;
                            break;
                    }
                }

                $relativeLogFilename = 'typo3temp/logs/sphinx.' . strtolower($action) . '.' . date('YmdHis') . '.log';
                $absoluteLogFilename = GeneralUtility::getFileAbsFileName($relativeLogFilename);
                Setup::dumpLog($absoluteLogFilename);

                // Reload the list of locally available versions of Sphinx
                $localVersions = Setup::getSphinxLocalVersions();
            } else {
                // Log operation?
                list($action, $file) = explode('-', $operation, 2);
                $fileName = PATH_site . 'typo3temp/logs/' . $file;

                if (preg_match('/^sphinx\.[^.]+\.\d+\.log$/', $file) && is_file($fileName)) {
                    switch ($action) {
                        case 'SHOWLOG':
                            $fileContents = file_get_contents($fileName);
                            $out[] = '<textarea style="width:100%;height:500px">' . htmlspecialchars($fileContents) . '</textarea>';
                            $out[] = '<p><a href="' . htmlspecialchars(GeneralUtility::linkThisScript()) . '">close</a></p>';

                            return implode(LF, $out);
                            break;
                        case 'REMOVELOG':
                            unlink($fileName);
                            break;
                    }
                }
            }
        }

        if (version_compare(TYPO3_version, '6.99.99', '<=')) {
            $out[] = '<style type="text/css">';
            $out[] = <<<CSS
form { position: relative; }

#typo3-extension-configuration-forms {
	max-width: inherit;
	padding: 0;
}

.leftColumn {
	width: 45em;
	margin-bottom: .5em;
}

@media screen and (min-width: 70em) {
	.rightColumn {
		position: absolute;
		top: 0;
		left: 46em;
		width: 44em;
	}
}
CSS;

            $out[] = '</style>';
        }

        $restToolsPath = GeneralUtility::getFileAbsFileName('uploads/tx_sphinx/RestTools');
        if (is_dir($restToolsPath)) {
            $relativePath = \TYPO3\CMS\Core\Utility\PathUtility::stripPathSitePrefix($restToolsPath);
            $out[] = $this->formatWarning('Please remove following directory: "<webroot>/' . $relativePath . '/" since it is not used anymore.', true);
        }

        $out[] = '<form action="' . htmlspecialchars(GeneralUtility::linkThisScript()) . '" method="post" class="container-fluid">';
        $out[] = '<div class="leftColumn col-md-6">';
        $out[] = '<p>Following versions of Sphinx may be installed locally:</p>';

        $out[] = '<table id="sphinx-versions" class="t3-table" style="width:auto">';
        $out[] = '<tr>';
        $out[] = '<th colspan="2">&nbsp;</th>';
        $out[] = '<th>1-click Process</th>';
        $out[] = '<th>Manual Process</th>';
        $out[] = '</tr>';

        $installRst2Pdf = TYPO3_OS !== 'WIN' && $this->configuration['install_rst2pdf'] === '1';
        $changes = array();

        if (version_compare(TYPO3_version, '7.6', '>=')) {
            /** @var \TYPO3\CMS\Core\Imaging\IconFactory $iconFactory */
            $iconFactory = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Imaging\\IconFactory');
        }

        foreach ($availableVersions as $version) {
            $isInstalled = in_array($version['key'], $localVersions);
            $hasSources = Setup::hasSphinxSources($version['key']);
            $hasLibraries = Setup::hasPyYaml()
                && Setup::hasPygments($version['key'])
                && Setup::hasT3SphinxThemeRtd()
                && Setup::hasT3FieldListTable()
                && Setup::hasT3TableRows()
                && Setup::hasT3Targets()
                && Setup::hasThirdPartyLibraries()
                && Setup::hasLaTeXPackage();
            if ($installRst2Pdf) {
                $hasLibraries &= Setup::hasPIL();
                $hasLibraries &= Setup::hasRst2Pdf();
            }

            $out[] = '<tr data-version="' . htmlspecialchars($version['key']) . '">';
            if (version_compare(TYPO3_version, '7.6', '>=')) {
                $iconChecked = $iconFactory->getIcon('status-status-checked', \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL)->render();
            } else {
                $iconChecked = \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon('status-status-checked');
            }
            $out[] = '<td>' . ($isInstalled ? $iconChecked : '') . '</td>';
            $out[] = '<td>';
            $out[] = 'Sphinx ' . htmlspecialchars($version['name']);
            $out[] = '</td>';

            $out[] = '<td><button name="operation" value="IMPORT-' . htmlspecialchars($version['name']) . '"' . ($isInstalled ? ' disabled="disabled"' : '') . '>import</button></td>';
            $out[] = '<td>';
            $out[] = '<button name="operation" value="DOWNLOAD-' . htmlspecialchars($version['name']) . '"' . ($hasSources && $hasLibraries ? ' disabled="disabled"' : '') . '>download</button>';
            $out[] = '<button name="operation" value="BUILD-' . htmlspecialchars($version['name']) . '"' . (!$hasSources ? ' disabled="disabled"' : '') . '>build</button>';
            $out[] = '<button name="operation" value="REMOVE-' . htmlspecialchars($version['name']) . '"' . (!($hasSources || $isInstalled) ? ' disabled="disabled"' : '') . '>remove</button>';
            $out[] = '</td>';
            $out[] = '</tr>';

            $changes[$version['key']] = Setup::getChanges($version['key']);
        }
        $out[] = '</table>';

        $logFiles = $this->getLogFiles();
        if (count($logFiles) > 0) {
            $out[] = '<p>Available logs:</p>';
            $out[] = '<table id="sphinx-versions" class="t3-table" style="width:auto">';
            $out[] = '<tr>';
            $out[] = '<th>Type</th>';
            $out[] = '<th>Date</th>';
            $out[] = '<th>Action</th>';
            $out[] = '</tr>';

            foreach ($logFiles as $logFile) {
                $out[] = '<tr>';
                $out[] = '<td>' . htmlspecialchars($logFile['type']) . '</td>';
                $out[] = '<td>' . date('d.m.Y H:i:s', $logFile['date']) . '</td>';

                $out[] = '<td>';
                $out[] = '<button name="operation" value="SHOWLOG-' . htmlspecialchars($logFile['file']) . '">open</button>';
                $out[] = '<button name="operation" value="REMOVELOG-' . htmlspecialchars($logFile['file']) . '">remove</button>';
                $out[] = '</td>';

                $out[] = '</tr>';
            }
            $out[] = '</table>';
            $out[] = '<ul>';

            $out[] = '</ul>';
        }

        $out[] = '</div>';    // .leftColumn
        $out[] = '<div id="sphinx-changes" class="rightColumn col-md-6"></div>';
        $out[] = '</form>';

        // JSON-encoding inspired by \TYPO3\CMS\Core\Utility\GeneralUtility::quoteJSvalue()
        $json = strtr(
            json_encode($changes, JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG),
            array(
                '"' => '\'',
                '\\\\' => '\\u005C',
                ' ' => '\\u0020',
                '!' => '\\u0021',
                '\\t' => '\\u0009',
                '\\n' => '\\u000A',
                '\\r' => '\\u000D'
            )
        );
        $out[] = '<script type="text/javascript">var sphinxChanges=' . $json . ';</script>';
        $out[] = '<script src="../typo3conf/ext/sphinx/Resources/Public/JavaScript/setup.js" type="text/javascript"></script>';

        return implode(LF, $out);
    }

    /**
     * Downloads Sphinx and associated libraries.
     *
     * @param array $data
     * @param array &$output
     * @return boolean true if operation succeeded, otherwise false
     */
    protected function downloadSphinx(array $data, array &$output)
    {
        $success = true;
        $installRst2Pdf = TYPO3_OS !== 'WIN' && $this->configuration['install_rst2pdf'] === '1';
        $version = $data['key'];
        $url = 'https://github.com' . $data['url'];

        if (!Setup::hasSphinxSources($version)) {
            $success &= Setup::downloadSphinxSources($version, $url, $output);
        }
        if ($installRst2Pdf && !Setup::hasPIL()) {
            $success &= Setup::downloadPIL($output);
        }
        if ($installRst2Pdf && !Setup::hasRst2Pdf()) {
            $success &= Setup::downloadRst2Pdf($output);
        }
        if (!Setup::hasPyYaml()) {
            $success &= Setup::downloadPyYaml($output);
        }
        if (!Setup::hasT3SphinxThemeRtd()) {
            $success &= Setup::downloadT3SphinxThemeRtd($output);
        }
        if (!Setup::hasT3FieldListTable()) {
            $success &= Setup::downloadT3FieldListTable($output);
        }
        if (!Setup::hasT3TableRows()) {
            $success &= Setup::downloadT3TableRows($output);
        }
        if (!Setup::hasT3Targets()) {
            $success &= Setup::downloadT3Targets($output);
        }
        if (!Setup::hasThirdPartyLibraries()) {
            $success &= Setup::downloadThirdPartyLibraries($output);
        }
        if (!Setup::hasPygments($version)) {
            $success &= Setup::downloadPygments($version, $output);
        }
        if (!Setup::hasLaTeXPackage()) {
            $success &= Setup::downloadLaTeXPackage($output);
        }

        return $success;
    }

    /**
     * Builds Sphinx associated libraries.
     *
     * @param array $data
     * @param array &$output
     * @return boolean true if operation succeeded, otherwise false
     */
    protected function buildSphinx(array $data, array &$output)
    {
        $success = false;
        $version = $data['key'];
        $installRst2Pdf = TYPO3_OS !== 'WIN' && $this->configuration['install_rst2pdf'] === '1';

        if (Setup::hasSphinxSources($version)) {
            $success = Setup::buildSphinx($version, $output);
            if ($success) {
                if ($installRst2Pdf && Setup::hasPIL()) {
                    $success &= Setup::buildPIL($version, $output);
                }
                if ($installRst2Pdf && Setup::hasRst2Pdf()) {
                    $success &= Setup::buildRst2Pdf($version, $output);
                }
                if (Setup::hasThirdPartyLibraries()) {
                    $selectedPlugins = GeneralUtility::trimExplode(',', $this->configuration['plugins'], true);
                    foreach ($selectedPlugins as $selectedPlugin) {
                        $success &= Setup::buildThirdPartyLibraries('sphinx-contrib' . DIRECTORY_SEPARATOR . $selectedPlugin, $version, $output);
                    }
                }
                if (Setup::hasPyYaml()) {
                    $success &= Setup::buildPyYaml($version, $output);
                }
                if (Setup::hasPygments($version)) {
                    $success &= Setup::buildPygments($version, $output);
                }
                if (Setup::hasT3SphinxThemeRtd()) {
                    $success &= Setup::buildT3SphinxThemeRtd($version, $output);
                }
                if (Setup::hasT3FieldListTable()) {
                    $success &= Setup::buildT3FieldListTable($version, $output);
                }
                if (Setup::hasT3TableRows()) {
                    $success &= Setup::buildT3TableRows($version, $output);
                }
                if (Setup::hasT3Targets()) {
                    $success &= Setup::buildT3Targets($version, $output);
                }
            }
        }

        return $success;
    }

    /**
     * Removes Sphinx (but not associated libraries).
     *
     * @param array $data
     * @param array &$output
     * @return void
     */
    protected function removeSphinx(array $data, array &$output)
    {
        $version = $data['key'];
        Setup::removeSphinx($version, $output);
    }

    /**
     * Creates an error message for backend output.
     *
     * @param string $message
     * @return string
     */
    protected function formatError($message)
    {
        $output = '<div class="typo3-message message-error">';
        //$output .= '<div class="message-header">Message head</div>';
        $output .= '<div class="message-body">' . nl2br(htmlspecialchars($message)) . '</div>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Creates a warning message for backend output.
     *
     * @param string $message
     * @param boolean $hsc
     * @return string
     */
    protected function formatWarning($message, $hsc = true)
    {
        $output = '<div class="typo3-message message-warning">';
        //$output .= '<div class="message-header">Message head</div>';
        if ($hsc) {
            $message = nl2br(htmlspecialchars($message));
        }
        $output .= '<div class="message-body">' . $message . '</div>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Creates an information message for backend output.
     *
     * @param string $message
     * @param boolean $hsc
     * @return string
     */
    protected function formatInformation($message, $hsc = true)
    {
        $output = '<div class="typo3-message message-information">';
        //$output .= '<div class="message-header">Message head</div>';
        if ($hsc) {
            $message = nl2br(htmlspecialchars($message));
        }
        $output .= '<div class="message-body">' . $message . '</div>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Creates an OK message for backend output.
     *
     * @param string $message
     * @param boolean $hsc
     * @return string
     */
    protected function formatOk($message, $hsc = true)
    {
        $output = '<div class="typo3-message message-ok">';
        //$output .= '<div class="message-header">Message head</div>';
        if ($hsc) {
            $message = nl2br(htmlspecialchars($message));
        }
        $output .= '<div class="message-body">' . $message . '</div>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Returns the Sphinx log files.
     *
     * @return array
     */
    protected function getLogFiles()
    {
        $sphinxFiles = array();

        $files = GeneralUtility::getFilesInDir(PATH_site . 'typo3temp/logs/');
        foreach ($files as $file) {
            if (preg_match('/^sphinx\.([^.]+)\.(\d{4})(\d\d)(\d\d)(\d\d)(\d\d)(\d\d)\.log$/', $file, $matches)) {
                $sphinxFile = array(
                    'file' => $file,
                    'type' => $matches[1],
                    'date' => mktime($matches[5], $matches[6], $matches[7], $matches[3], $matches[4], $matches[2]),
                );

                $key = $sphinxFile['date'] . '-' . $sphinxFile['type'];
                $sphinxFiles[$key] = $sphinxFile;
            }
        }

        krsort($sphinxFiles);
        return array_values($sphinxFiles);
    }

}
