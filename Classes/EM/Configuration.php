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

namespace Causal\Sphinx\EM;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\CommandUtility;
use Causal\Sphinx\Utility\Setup;

/**
 * Configuration class for the TYPO3 Extension Manager.
 *
 * @category    Extension Manager
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Configuration
{

    /** @var string */
    protected $extKey = 'sphinx';

    /**
     * Returns an Extension Manager field for selecting the Sphinx version to use.
     *
     * @param array $params
     * @param \TYPO3\CMS\Extensionmanager\ViewHelpers\Form\TypoScriptConstantsViewHelper $pObj
     * @return string
     */
    public function getVersions(array $params, \TYPO3\CMS\Extensionmanager\ViewHelpers\Form\TypoScriptConstantsViewHelper $pObj)
    {
        $out = array();
        $globalVersion = null;

        $sphinxPath = GeneralUtility::getFileAbsFileName('typo3temp/tx_sphinx/sphinx-doc');
        $versions = array();
        if (is_dir($sphinxPath)) {
            $versions = GeneralUtility::get_dirs($sphinxPath);
        }
        $versions = array_diff($versions, array('bin'));

        // Maybe a global install of Sphinx is available
        $sphinxBuilder = CommandUtility::getCommand('sphinx-build');
        // Do not resolve symbolic link here, no need if after all one wants to link to a local version of sphinx-build
        if ($sphinxBuilder && !GeneralUtility::isFirstPartOfStr($$sphinxBuilder, $sphinxPath)) {
            $output = array();
            CommandUtility::exec(escapeshellarg($sphinxBuilder) . ' --version 2>&1', $output);
            $versionLine = $output[0];
            $versionParts = explode(' ', $versionLine);
            $globalVersion = end($versionParts);
            array_unshift($versions, 'SYSTEM');
        }

        if (!$versions) {
            $emLink = \Causal\Sphinx\Utility\MiscUtility::getExtensionManagerLink('sphinx', 'UpdateScript', 'show');
            $message = sprintf('Please <a href="%s">run Update script</a> first.', $emLink);

            $out[] = '<div class="typo3-message message-warning">';
            $out[] = '<div class="message-header">No versions of Sphinx available</div>';
            $out[] = '<div class="message-body">' . $message . '</div>';
            $out[] = '</div>';
        }

        $selectedVersion = $params['fieldValue'];

        if ($selectedVersion && $selectedVersion !== 'SYSTEM' && is_dir($sphinxPath . '/bin')) {
            // Recreate the shortcut links to selected version
            // /path/to/sphinx-doc/sphinx-build -> /path/to/sphinx-doc/sphinx-build-1.2b1
            $scripts = array(
                'sphinx-build',
                'sphinx-quickstart',
            );
            chdir($sphinxPath . '/bin');
            foreach ($scripts as $script) {
                $scriptFilename = $sphinxPath . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . $script;
                $scriptVersion = $script . '-' . $selectedVersion;

                if (TYPO3_OS === 'WIN') {
                    $scriptFilename .= '.bat';
                    $scriptVersion .= '.bat';

                    @unlink($scriptFilename);
                    copy($scriptVersion, $scriptFilename);
                } else {
                    @unlink($scriptFilename);
                    symlink($scriptVersion, $script);
                }
            }
        }

        if (count($versions) > 0) {
            // Sphinx actually relies by default on Jinja2 as templating engine and Jinja2 requires Python 2.6
            // We don't check this as a hard requirement but will issue a warning
            $pythonVersion = Setup::getPythonVersion();
            if (version_compare($pythonVersion, '2.6', '<')) {
                $out[] = '<div class="typo3-message message-warning">';
                //$out[] = '<div class="message-header">Message head</div>';
                $out[] = '<div class="message-body">';
                $out[] = '<strong>Beware:</strong> The default templating language in Sphinx is Jinja. Jinja requires ' .
                    'at least Python 2.6 but you are using ' . $pythonVersion . '. As such it is very likely that you ' .
                    'will not be able to actually render Sphinx projects.';
                $out[] = '</div>';
                $out[] = '</div>';
            }
        }

        $i = 0;
        foreach ($versions as $version) {
            $out[] = '<div style="margin-top:1ex">';
            $label = $version !== 'SYSTEM' ? $version : $globalVersion . ' (system)';
            // Make sure main release (e.g., "1.2") gets a ".0" patch release version as well
            if (preg_match('/^\d+\.\d+$/', $label)) {
                $label .= '.0';
            }
            $label = str_replace(['a', 'b'], [' alpha ', ' beta '], $label);
            $checked = $version === $selectedVersion ? ' checked="checked"' : '';
            $out[] = '<input type="radio" id="sphinx_version_' . $i . '" name="sphinx_version" value="' . $version . '"' . $checked . ' onclick="toggleSphinxVersion();" />';
            $out[] = '<label for="sphinx_version_' . $i . '" style="display:inline">' . $label . '</label>';
            $out[] = '</div>';
            $i++;
        }

        $fieldId = str_replace(array('[', ']'), '_', $params['fieldName']);
        $out[] = '<script type="text/javascript">';
        $out[] = <<<JS

function toggleSphinxVersion() {
    var versions = document.getElementsByName('sphinx_version');
    for (var i = 0; i < versions.length; i++) {
        if (versions[i].checked) {
            document.getElementById("{$fieldId}").value = versions[i].value;
        }
    }
}

JS;
        $out[] = '</script>';
        $out[] = '<input type="hidden" id="' . $fieldId . '" name="' . $params['fieldName'] . '" value="' . $params['fieldValue'] . '" />';

        return implode(LF, $out);
    }

    /**
     * Returns an Extension Manager field for selecting the 3rd-party Sphinx plugins to use.
     *
     * @param array $params
     * @param \TYPO3\CMS\Extensionmanager\ViewHelpers\Form\TypoScriptConstantsViewHelper $pObj
     * @return string
     */
    public function getPlugins(array $params, \TYPO3\CMS\Extensionmanager\ViewHelpers\Form\TypoScriptConstantsViewHelper $pObj)
    {
        $out = array();
        $plugins = Setup::getAvailableThirdPartyPlugins();

        $out[] = '<div class="typo3-message message-warning">';
        //$out[] = '<div class="message-header">Message head</div>';
        $out[] = '<div class="message-body">';
        $out[] = '<strong>Beware:</strong> Make sure to use only plugins available on docs.typo3.org if you plan to publish documents.';
        $out[] = '</div>';
        $out[] = '</div>';

        $out[] = '<div class="typo3-message message-information">';
        //$out[] = '<div class="message-header">Message head</div>';
        $out[] = '<div class="message-body">';
        $sphinxVersion = \Causal\Sphinx\Utility\SphinxBuilder::getSphinxVersion();
        // Fix parameter value for alpha/beta releases
        $sphinxVersion = str_replace(['a', 'b'], [' alpha ', ' beta '], $sphinxVersion);
        $emLink = \Causal\Sphinx\Utility\MiscUtility::getExtensionManagerLink(
            'sphinx',
            'UpdateScript',
            'show',
            array(
                'operation' => 'BUILD-' . $sphinxVersion,
            )
        );
        $out[] = sprintf('Please <a href="%s">rebuild your Sphinx environment</a> after activating plugins.', $emLink);
        $out[] = '</div>';
        $out[] = '</div>';

        $selectedPlugins = GeneralUtility::trimExplode(',', $params['fieldValue'], true);

        // First show plugins available on docs.typo3.org, then the others
        $sortedPlugins = array('start' => array(), 'end' => array());
        foreach ($plugins as $plugin) {
            if ($plugin['docst3o']) {
                $sortedPlugins['start'][] = $plugin;
            } else {
                $sortedPlugins['end'][] = $plugin;
            }
        }
        $plugins = array_merge($sortedPlugins['start'], $sortedPlugins['end']);

        $i = 0;
        foreach ($plugins as $plugin) {
            $out[] = '<div style="margin-top:1ex">';
            $label = 'sphinxcontrib.' . $plugin['name'];
            $checked = in_array($plugin['name'], $selectedPlugins) ? ' checked="checked"' : '';

            $out[] = '<input type="checkbox" id="sphinx_plugin_' . $i . '" name="sphinx_plugin" value="' . htmlspecialchars($plugin['name']) . '"' . $checked . ' onclick="toggleSphinxPlugin();" />';
            $out[] = '<label for="sphinx_plugin_' . $i . '" style="display:inline"><strong>' . $label . '</strong></label>';
            if ($plugin['docst3o']) {
                // Plugin is available on docs.typo3.org
                $imaget3o = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($this->extKey) .
                    'Resources/Public/Images/docst3o.png';
                $label = 'Plugin is available on docs.typo3.org';
                $out[] = sprintf(
                    ' <img src="%s" alt="%s" title="%s" />',
                    $imaget3o,
                    htmlspecialchars($label),
                    htmlspecialchars($label)
                );
            }
            $out[] = '<div style="margin-left:1.5em">';
            $out[] = htmlspecialchars($plugin['description'] ?: 'n/a');
            $out[] = '<br /><a href="' . $plugin['readme'] . '" target="_sphinx-plugin" title="Read documentation">documentation</a>';
            $out[] = '</div>';
            $out[] = '</div>';
            $i++;
        }

        $fieldId = str_replace(array('[', ']'), '_', $params['fieldName']);
        $out[] = '<script type="text/javascript">';
        $out[] = <<<JS

function toggleSphinxPlugin() {
    var plugins = document.getElementsByName('sphinx_plugin');
    var selectedPlugins = [];
    for (var i = 0; i < plugins.length; i++) {
        if (plugins[i].checked) {
            selectedPlugins.push(plugins[i].value);
        }
    }
    document.getElementById("{$fieldId}").value = selectedPlugins.join(',');
}

JS;
        $out[] = '</script>';
        $out[] = '<input type="hidden" id="' . $fieldId . '" name="' . $params['fieldName'] . '" value="' . $params['fieldValue'] . '" />';

        return implode(LF, $out);
    }

}
