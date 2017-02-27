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

namespace Causal\Sphinx\Utility;

use TYPO3\CMS\Core\Utility\CommandUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * Sphinx environment setup.
 *
 * @category    Utility
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Setup
{

    /** @var string */
    protected static $extKey = 'sphinx';

    /** @var array */
    protected static $log = array();

    /**
     * Returns the version of python.
     *
     * @return string The version of python
     */
    public static function getPythonVersion()
    {
        $version = null;
        if (CommandUtility::checkCommand('python')) {
            $python = escapeshellarg(CommandUtility::getCommand('python'));
            $cmd = $python . ' -V 2>&1';
            static::exec($cmd, $out, $ret);
            if ($ret === 0) {
                $versionLine = array_shift($out);
                if (preg_match('/Python ([0-9.]+)/', $versionLine, $matches)) {
                    $version = $matches[1];
                }
            }
        }
        return $version;
    }

    /**
     * Initializes the environment by creating directories to hold sphinx and 3rd
     * party tools.
     *
     * @return array Error messages, if any
     */
    public static function createLibraryDirectories()
    {
        $errors = array();

        if ($GLOBALS['TYPO3_CONF_VARS']['BE']['disable_exec_function'] == 1) {
            $errors[] = 'You have disabled exec() with $TYPO3_CONF_VARS[\'BE\'][\'disable_exec_function\'] = \'1\'. ' .
                'Please open System > Install > All configuration and set it to 0 to proceed.';
            return $errors;
        }

        if (!CommandUtility::checkCommand('python')) {
            $errors[] = 'Python interpreter was not found. Hint: You probably should double-check ' .
                '$TYPO3_CONF_VARS[\'SYS\'][\'binPath\'] and/or $TYPO3_CONF_VARS[\'SYS\'][\'binSetup\'].';
        }
        if (!CommandUtility::checkCommand('unzip')) {
            $errors[] = 'Unzip cannot be executed. Hint: You probably should double-check ' .
                '$TYPO3_CONF_VARS[\'SYS\'][\'binPath\'] and/or $TYPO3_CONF_VARS[\'SYS\'][\'binSetup\'].';
        }

        $directories = array(
            'typo3temp/tx_sphinx/sphinx-doc/',
            'typo3temp/tx_sphinx/sphinx-doc/bin/',
            'uploads/tx_sphinx/',
        );
        foreach ($directories as $directory) {
            $absoluteDirectory = GeneralUtility::getFileAbsFileName($directory);
            if (!is_dir($absoluteDirectory)) {
                GeneralUtility::mkdir_deep($absoluteDirectory);
            }
            if (is_dir($absoluteDirectory)) {
                if (!is_writable($absoluteDirectory)) {
                    $errors[] = 'Directory ' . $absoluteDirectory . ' is read-only.';
                }
            } else {
                $errors[] = 'Cannot create directory ' . $absoluteDirectory . '.';
            }
        }

        return $errors;
    }

    /**
     * Returns true if the source files of Sphinx are available locally.
     *
     * @param string $version Version name (e.g., 1.0.0)
     * @return bool
     */
    public static function hasSphinxSources($version)
    {
        $sphinxSourcesPath = static::getSphinxSourcesPath();
        $setupFile = $sphinxSourcesPath . $version . '/setup.py';
        return is_file($setupFile);
    }

    /**
     * Downloads the source files of Sphinx.
     *
     * @param string $version Version name (e.g., 1.0.0)
     * @param string $url Complete URL of the zip file containing the sphinx sources
     * @param null|array $output Log of operations
     * @return bool true if operation succeeded, otherwise false
     * @throws \Exception
     * @see https://github.com/sphinx-doc/sphinx
     */
    public static function downloadSphinxSources($version, $url, array &$output = null)
    {
        $success = true;
        $tempPath = MiscUtility::getTemporaryPath();
        $sphinxSourcesPath = static::getSphinxSourcesPath();

        // There is a redirect from the URI in the web interface. E.g.,
        // https://github.com/sphinx-doc/sphinx/archive/1.3.zip
        // and the actual download link:
        // https://codeload.github.com/sphinx-doc/sphinx/zip/1.3
        if (preg_match('#https://github.com/sphinx-doc/sphinx/archive/([0-9b.]+?)\\.zip#', $url, $matches)) {
            $url = 'https://codeload.github.com/sphinx-doc/sphinx/zip/' . $matches[1];
        }

        $zipFilename = $tempPath . $version . '.zip';
        static::$log[] = '[INFO] Fetching ' . $url;
        $zipContent = MiscUtility::getUrl($url);
        if ($zipContent && GeneralUtility::writeFile($zipFilename, $zipContent)) {
            $output[] = '[INFO] Sphinx ' . $version . ' has been downloaded.';
            $targetPath = $sphinxSourcesPath . $version;

            // Unzip the Sphinx archive
            $out = array();
            if (static::unarchive($zipFilename, $targetPath, 'sphinx-' . $version)) {
                $output[] = '[INFO] Sphinx ' . $version . ' has been unpacked.';

                // Patch Sphinx to let us get colored output
                $sourceFilename = $targetPath . '/sphinx/util/console.py';

                // Compatibility with Windows platform
                $sourceFilename = str_replace('/', DIRECTORY_SEPARATOR, $sourceFilename);

                if (file_exists($sourceFilename)) {
                    static::$log[] = '[INFO] Patching file ' . $sourceFilename;
                    $contents = file_get_contents($sourceFilename);
                    $contents = str_replace(
                        'def color_terminal():',
                        "def color_terminal():\n    if 'COLORTERM' in os.environ:\n        return True",
                        $contents
                    );
                    GeneralUtility::writeFile($sourceFilename, $contents);
                }
            } else {
                $success = false;
                $output[] = '[ERROR] Could not extract Sphinx ' . $version . ':' . LF . LF . implode($out, LF);
            }
        } else {
            $success = false;
            $output[] = '[ERROR] Cannot fetch file ' . $url . '.';
        }

        return $success;
    }

    /**
     * Builds and installs Sphinx locally.
     *
     * @param string $version Version name (e.g., 1.0.0)
     * @param null|array $output Log of operations
     * @return bool true if operation succeeded, otherwise false
     * @throws \Exception
     */
    public static function buildSphinx($version, array &$output = null)
    {
        $success = true;
        $sphinxSourcesPath = static::getSphinxSourcesPath();
        $sphinxPath = static::getSphinxPath();

        // Sphinx 1.2 requires Python 2.5
        // https://forge.typo3.org/issues/53246
        if (version_compare($version, '1.1.99', '>')) {
            $pythonVersion = static::getPythonVersion();
            if (version_compare($pythonVersion, '2.5', '<')) {
                $success = false;
                $output[] = '[ERROR] Could not install Sphinx ' . $version . ': You are using Python ' . $pythonVersion .
                    ' but the required version is at least 2.5.';
                return $success;
            }
        }

        $pythonHome = null;
        $pythonLib = null;
        $setupFile = $sphinxSourcesPath . $version . DIRECTORY_SEPARATOR . 'setup.py';

        if (is_file($setupFile)) {
            $python = escapeshellarg(CommandUtility::getCommand('python'));
            $cmd = 'cd ' . escapeshellarg(PathUtility::dirname($setupFile)) . ' && ' .
                $python . ' setup.py clean 2>&1 && ' .
                $python . ' setup.py build 2>&1';
            $out = array();
            static::exec($cmd, $out, $ret);
            if ($ret === 0) {
                $pythonHome = $sphinxPath . $version;
                $pythonLib = $pythonHome . '/lib/python';

                // Compatibility with Windows platform
                $pythonLib = str_replace('/', DIRECTORY_SEPARATOR, $pythonLib);
                $safePythonLib = strpos($pythonLib, ' ') !== false
                    ? escapeshellarg($pythonLib)
                    : $pythonLib;

                static::$log[] = '[INFO] Recreating directory ' . $pythonHome;
                GeneralUtility::rmdir($pythonHome, true);
                GeneralUtility::mkdir_deep($pythonLib . DIRECTORY_SEPARATOR);

                $cmd = 'cd ' . escapeshellarg(PathUtility::dirname($setupFile)) . ' && ' .
                    MiscUtility::getExportCommand('PYTHONPATH', $safePythonLib) . ' && ' .
                    $python . ' setup.py install --home=' . escapeshellarg($pythonHome) . ' 2>&1';
                $out = array();
                static::exec($cmd, $out, $ret);
                if ($ret === 0) {
                    $output[] = '[OK] Sphinx ' . $version . ' has been successfully installed.';
                } else {
                    $success = false;
                    $output[] = '[ERROR] Could not install Sphinx ' . $version . ':' . LF . LF . implode($out, LF);
                }
            } else {
                $success = false;
                $output[] = '[ERROR] Could not build Sphinx ' . $version . ':' . LF . LF . implode($out, LF);
            }
        } else {
            $success = false;
            $output[] = '[ERROR] Setup file ' . $setupFile . ' was not found.';
        }

        if ($success) {
            $shortcutScripts = array(
                'sphinx-build',
                'sphinx-quickstart',
            );
            $pythonPath = $sphinxPath . $version . '/lib/python';

            // Compatibility with Windows platform
            $pythonPath = str_replace('/', DIRECTORY_SEPARATOR, $pythonPath);

            foreach ($shortcutScripts as $shortcutScript) {
                $shortcutFilename = $sphinxPath . 'bin' . DIRECTORY_SEPARATOR . $shortcutScript . '-' . $version;
                $scriptFilename = $sphinxPath . $version . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . $shortcutScript;

                if (TYPO3_OS === 'WIN') {
                    $shortcutFilename .= '.bat';
                    $scriptFilename .= '.exe';

                    $script = <<<EOT
@ECHO OFF
SET PYTHONPATH=$pythonPath

$scriptFilename %*
EOT;
                    // Use CRLF under Windows
                    $script = str_replace(CR, LF, $script);
                    $script = str_replace(LF, CR . LF, $script);
                } else {
                    $script = <<<EOT
#!/bin/bash

export PYTHONPATH=$pythonPath

$scriptFilename "\$@"
EOT;
                }

                GeneralUtility::writeFile($shortcutFilename, $script);
                chmod($shortcutFilename, 0755);
            }
        }

        return $success;
    }

    /**
     * Removes a local version of Sphinx (sources + build).
     *
     * @param string $version Version name (e.g., "1.0.0")
     * @param null|array $output Log of operations
     * @return void
     */
    public static function removeSphinx($version, array &$output = null)
    {
        $sphinxSourcesPath = static::getSphinxSourcesPath();
        $sphinxPath = static::getSphinxPath();

        if (is_dir($sphinxSourcesPath . $version)) {
            if (GeneralUtility::rmdir($sphinxSourcesPath . $version, true)) {
                $output[] = '[OK] Sources of Sphinx ' . $version . ' have been deleted.';
            } else {
                $output[] = '[ERROR] Could not delete sources of Sphinx ' . $version . '.';
            }
        }
        if (is_dir($sphinxPath . $version)) {
            if (GeneralUtility::rmdir($sphinxPath . $version, true)) {
                $output[] = '[OK] Sphinx ' . $version . ' has been deleted.';
            } else {
                $output[] = '[ERROR] Could not delete Sphinx ' . $version . '.';
            }
        }

        $shortcutScripts = array(
            'sphinx-build-' . $version,
            'sphinx-quickstart-' . $version,
        );
        foreach ($shortcutScripts as $shortcutScript) {
            $shortcutFilename = $sphinxPath . 'bin' . DIRECTORY_SEPARATOR . $shortcutScript;

            if (TYPO3_OS === 'WIN') {
                $shortcutFilename .= '.bat';
            }

            if (is_file($shortcutFilename)) {
                @unlink($shortcutFilename);
            }
        }
    }

    /**
     * Returns true if the source files of the t3SphinxThemeRtd package are available locally.
     *
     * @return bool
     */
    public static function hasT3SphinxThemeRtd()
    {
        $sphinxSourcePath = static::getSphinxSourcesPath();
        $setupFile = $sphinxSourcePath . 't3SphinxThemeRtd/setup.py';
        return is_file($setupFile);
    }

    /**
     * Downloads the source files of the t3SphinxThemeRtd package.
     *
     * @param array|null $output
     * @return bool true if operation succeeded, otherwise false
     * @throws \Exception
     */
    public static function downloadT3SphinxThemeRtd(array &$output = null)
    {
        $success = static::cloneGitHubProject('https://github.com/TYPO3-Documentation/t3SphinxThemeRtd.git', $output, 't3SphinxThemeRtd');
        return $success;
    }

    /**
     * Builds the source files of the t3SphinxThemeRtd package.
     *
     * @param string $sphinxVersion
     * @param array|null $output
     * @return bool true if operation succeeded, otherwise false
     * @throws \Exception
     */
    public static function buildT3SphinxThemeRtd($sphinxVersion, array &$output = null)
    {
        $success = static::buildThirdPartyLibraries('t3SphinxThemeRtd', $sphinxVersion, $output);
        return $success;
    }

    /**
     * Returns true if the source files of the sphinxcontrib.t3fieldlisttable package are available locally.
     *
     * @return bool
     */
    public static function hasT3FieldListTable()
    {
        $sphinxSourcePath = static::getSphinxSourcesPath();
        $setupFile = $sphinxSourcePath . 'sphinxcontrib.t3fieldlisttable/setup.py';
        return is_file($setupFile);
    }

    /**
     * Downloads the source files of the sphinxcontrib.t3fieldlisttable package.
     *
     * @param array|null $output
     * @return bool true if operation succeeded, otherwise false
     * @throws \Exception
     */
    public static function downloadT3FieldListTable(array &$output = null)
    {
        $success = static::cloneGitHubProject('https://github.com/TYPO3-Documentation/sphinxcontrib.t3fieldlisttable.git', $output, 'sphinxcontrib.t3fieldlisttable');
        return $success;
    }

    /**
     * Builds the source files of the sphinxcontrib.t3fieldlisttable package.
     *
     * @param string $sphinxVersion
     * @param array|null $output
     * @return bool true if operation succeeded, otherwise false
     * @throws \Exception
     */
    public static function buildT3FieldListTable($sphinxVersion, array &$output = null)
    {
        $success = static::buildThirdPartyLibraries('sphinxcontrib.t3fieldlisttable', $sphinxVersion, $output);
        return $success;
    }

    /**
     * Returns true if the source files of the sphinxcontrib.t3tablerows package are available locally.
     *
     * @return bool
     */
    public static function hasT3TableRows()
    {
        $sphinxSourcePath = static::getSphinxSourcesPath();
        $setupFile = $sphinxSourcePath . 'sphinxcontrib.t3tablerows/setup.py';
        return is_file($setupFile);
    }

    /**
     * Downloads the source files of the sphinxcontrib.t3tablerows package.
     *
     * @param array|null $output
     * @return bool true if operation succeeded, otherwise false
     * @throws \Exception
     */
    public static function downloadT3TableRows(array &$output = null)
    {
        $success = static::cloneGitHubProject('https://github.com/TYPO3-Documentation/sphinxcontrib.t3tablerows.git', $output, 'sphinxcontrib.t3tablerows');
        return $success;
    }

    /**
     * Builds the source files of the sphinxcontrib.t3tablerows package.
     *
     * @param string $sphinxVersion
     * @param array|null $output
     * @return bool true if operation succeeded, otherwise false
     * @throws \Exception
     */
    public static function buildT3TableRows($sphinxVersion, array &$output = null)
    {
        $success = static::buildThirdPartyLibraries('sphinxcontrib.t3tablerows', $sphinxVersion, $output);
        return $success;
    }

    /**
     * Returns true if the source files of the sphinxcontrib.t3targets package are available locally.
     *
     * @return bool
     */
    public static function hasT3Targets()
    {
        $sphinxSourcePath = static::getSphinxSourcesPath();
        $setupFile = $sphinxSourcePath . 'sphinxcontrib.t3targets/setup.py';
        return is_file($setupFile);
    }

    /**
     * Downloads the source files of the sphinxcontrib.t3targets package.
     *
     * @param array|null $output
     * @return bool true if operation succeeded, otherwise false
     * @throws \Exception
     */
    public static function downloadT3Targets(array &$output = null)
    {
        $success = static::cloneGitHubProject('https://github.com/TYPO3-Documentation/sphinxcontrib.t3targets.git', $output, 'sphinxcontrib.t3targets');
        return $success;
    }

    /**
     * Builds the source files of the sphinxcontrib.t3targets package.
     *
     * @param string $sphinxVersion
     * @param array|null $output
     * @return bool true if operation succeeded, otherwise false
     * @throws \Exception
     */
    public static function buildT3Targets($sphinxVersion, array &$output = null)
    {
        $success = static::buildThirdPartyLibraries('sphinxcontrib.t3targets', $sphinxVersion, $output);
        return $success;
    }

    /**
     * Returns true if the source files of 3rd-party libraries are available locally.
     *
     * @return bool
     */
    public static function hasThirdPartyLibraries()
    {
        $sphinxSourcesPath = static::getSphinxSourcesPath();
        $setupFile = $sphinxSourcesPath . 'sphinx-contrib/make-ext.py';
        return is_file($setupFile);
    }

    /**
     * Downloads the source files of 3rd-party libraries.
     *
     * @param array|null $output Log of operations
     * @return bool true if operation succeeded, otherwise false
     * @throws \Exception
     * @see https://bitbucket.org/xperseguers/sphinx-contrib/
     */
    public static function downloadThirdPartyLibraries(array &$output = null)
    {
        $success = true;
        $tempPath = MiscUtility::getTemporaryPath();
        $sphinxSourcesPath = static::getSphinxSourcesPath();

        if (!CommandUtility::checkCommand('unzip')) {
            $success = false;
            $output[] = '[WARNING] Could not find command unzip. 3rd-party libraries were not installed.';
        } else {
            $url = 'https://bitbucket.org/xperseguers/sphinx-contrib/downloads/';
            $content = MiscUtility::getUrl($url);
            $content = substr($content, strpos($content, '<dl class="metadata">'));
            // Search for the download link
            // <a rel="nofollow"
            //        href="/xperseguers/sphinx-contrib/get/a3d904f8ab24.zip"
            // >(download)</a>
            if (preg_match('#href="(/xperseguers/sphinx-contrib/get/[0-9a-f]+\.zip)"#', $content, $matches)) {
                $url = 'https://bitbucket.org' . $matches[1];
                $archiveFilename = $tempPath . 'sphinx-contrib.zip';
                $archiveContent = MiscUtility::getUrl($url);
                if ($archiveContent && GeneralUtility::writeFile($archiveFilename, $archiveContent)) {
                    $output[] = '[INFO] 3rd-party libraries for Sphinx have been downloaded.';

                    $targetPath = $sphinxSourcesPath . 'sphinx-contrib';

                    // Unpack 3rd-party libraries archive
                    $out = array();
                    if (static::unarchive($archiveFilename, $targetPath, 'xperseguers-sphinx-contrib-', $out)) {
                        $output[] = '[INFO] 3rd-party libraries for Sphinx have been unpacked.';
                    } else {
                        $success = false;
                        $output[] = '[ERROR] Could not extract 3rd-party libraries for Sphinx:' . LF . LF . implode($out, LF);
                    }
                } else {
                    $success = false;
                    $output[] = '[ERROR] Could not download ' . htmlspecialchars($url);
                }
            } else {
                $success = false;
                $output[] = '[ERROR] Could not fetch ' . htmlspecialchars($url);
            }
        }

        return $success;
    }

    /**
     * Builds and installs 3rd-party libraries locally.
     *
     * @param string $package The python package to build
     * @param string $sphinxVersion The Sphinx version to build 3rd-party libraries for
     * @param array|null $output Log of operations
     * @return bool true if operation succeeded, otherwise false
     * @throws \Exception
     */
    public static function buildThirdPartyLibraries($package, $sphinxVersion, array &$output = null)
    {
        $sphinxSourcesPath = static::getSphinxSourcesPath();
        $sphinxPath = static::getSphinxPath();

        $pythonHome = $sphinxPath . $sphinxVersion;
        $pythonLib = $pythonHome . '/lib/python';

        // Compatibility with Windows platform
        $pythonHome = str_replace('/', DIRECTORY_SEPARATOR, $pythonHome);
        $pythonLib = str_replace('/', DIRECTORY_SEPARATOR, $pythonLib);

        if (!is_dir($pythonLib)) {
            $success = false;
            $output[] = '[ERROR] Invalid Python library: ' . $pythonLib;
            return $success;
        }

        $setupFile = $sphinxSourcesPath . $package . DIRECTORY_SEPARATOR . 'setup.py';
        if (is_file($setupFile)) {
            $success = static::buildWithPython(
                'Package "' . $package . '"',
                $setupFile,
                $pythonHome,
                $pythonLib,
                '',
                $output
            );

            // On some platforms the library is compiled e.g., within "lib.linux-x86_64-2.7" but some parts of this
            // extension (like loading the jQuery JS library that comes with the template) expect a "lib" directory.
            $buildPath = $sphinxSourcesPath . $package . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR;
            if (!is_dir($buildPath . 'lib')) {
                $directories = GeneralUtility::get_dirs($buildPath);
                foreach ($directories as $directory) {
                    if (GeneralUtility::isFirstPartOfStr($directory, 'lib.')) {
                        if (TYPO3_OS === 'WIN') {
                            GeneralUtility::mkdir($buildPath . 'lib');
                            MiscUtility::recursiveCopy($buildPath . $directory, $buildPath . 'lib');
                        } else {
                            chdir($buildPath);
                            symlink($directory, 'lib');
                        }
                        break;
                    }
                }
            }
        } else {
            $success = false;
            $output[] = '[ERROR] Setup file ' . $setupFile . ' was not found.';
        }

        return $success;
    }

    /**
     * Returns a list of available 3rd-party plugins.
     *
     * @return array
     */
    public static function getAvailableThirdPartyPlugins()
    {
        $sphinxSourcesPath = static::getSphinxSourcesPath();
        $pluginsPath = $sphinxSourcesPath . 'sphinx-contrib/';
        $plugins = array();

        $descriptions = array(
            'aafig' => 'render embeded ASCII art as nice images using aafigure.',
            'actdiag' => 'embed activity diagrams by using actdiag',
            'adadomain' => 'an extension for Ada support (Sphinx 1.0 needed)',
            'ansi' => 'parse ANSI color sequences inside documents',
            'autoprogram' => 'documenting CLI programs',
            'autorun' => 'Execute code in a runblock directive.',
            'blockdiag' => 'embed block diagrams by using blockdiag',
            'cheeseshop' => 'easily link to PyPI packages',
            'clearquest' => 'create tables from ClearQuest queries.',
            'cmakedomain' => 'a domain for CMake',
            'coffeedomain' => 'a domain for (auto)documenting CoffeeScript source code.',
            'context' => 'a builder for ConTeXt.',
            'doxylink' => 'Link to external Doxygen-generated HTML documentation',
            'domaintools' => 'A tool for easy domain creation',
            'email' => 'obfuscate email addresses',
            'erlangdomain' => 'an extension for Erlang support (Sphinx 1.0 needed)',
            'exceltable' => 'embed Excel spreadsheets into documents using exceltable',
            'feed' => 'an extension for creating syndication feeds and time-based overviews from your site content',
            'findanything' => 'an extension to add Sublime Text 2 like findanything panel to your documentation to find pages, sections and index entries while typing',
            'gnuplot' => 'produces images using gnuplot language.',
            'googleanalytics' => 'track html visitors statistics',
            'googlechart' => 'embed charts by using Google Chart_',
            'googlemaps' => 'embed maps by using Google Maps_',
            'httpdomain' => 'a domain for documenting RESTful HTTP APIs.',
            'hyphenator' => 'client-side hyphenation of HTML using hyphenator',
            'inlinesyntaxhighlight' => 'inline syntax highlighting',
            'lassodomain' => 'a domain for documenting Lasso source code',
            'lilypond' => 'an extension inserting music scripts from Lilypond in PNG format.',
            'makedomain' => 'a domain for GNU Make',
            'matlabdomain' => 'document MATLAB and GNU Octave code.',
            'mockautodoc' => 'mock imports.',
            'mscgen' => 'embed mscgen-formatted MSC (Message Sequence Chart)s.',
            'napoleon' => 'supports Google style and NumPy style docstrings.',
            'nicoviceo' => 'embed videos from nicovideo',
            'numfig' => 'numbered figures',
            'nwdiag' => 'embed network diagrams by using nwdiag',
            'omegat' => 'support tools to collaborate with OmegaT (Sphinx 1.1 needed)',
            'osaka' => 'convert standard Japanese doc to Osaka dialect (it is joke extension)',
            'paverutils' => 'an alternate integration of Sphinx with Paver.',
            'phpdomain' => 'an extension for PHP support',
            'plantuml' => 'embed UML diagram by using PlantUML',
            'py_directive' => 'Execute python code in a py directive and return a math node.',
            'rawfiles' => 'copy raw files, like a CNAME.',
            'requirements' => 'declare requirements wherever you need (e.g. in test docstrings), mark statuses and collect them in a single list',
            'restbuilder' => 'a builder for reST (reStructuredText) files.',
            'rubydomain' => 'an extension for Ruby support (Sphinx 1.0 needed)',
            'sadisplay' => 'display SqlAlchemy model sadisplay',
            'sdedit' => 'an extension inserting sequence diagram by using Quick Sequence. Diagram Editor (sdedit)',
            'seqdiag' => 'embed sequence diagrams by using seqdiag',
            'slide' => 'embed presentation slides on slideshare and other sites.',
            'swf' => 'embed flash files',
            'sword' => 'an extension inserting Bible verses from Sword.',
            'tikz' => 'draw pictures with the TikZ/PGF LaTeX package.',
            'traclinks' => 'create TracLinks to a Trac instance from within Sphinx',
            'whooshindex' => 'whoosh indexer extension',
            'youtube' => 'embed videos from YouTube',
            'zopeext' => 'provide an autointerface directive for using Zope interfaces.',
        );

        // We have no official list but Xavier Perseguers (@xperseguers) takes care
        // of maintaining this list
        $availableOnDocsTypo3Org = array(
            'googlechart',
            'googlemaps',
            'httpdomain',
            'numfig',
            'slide',
            'youtube',
        );

        $directories = GeneralUtility::get_dirs($pluginsPath);
        if (is_array($directories)) {
            foreach ($directories as $directory) {
                if ($directory{0} === '_' || !is_file($pluginsPath . $directory . '/README.rst')) {
                    continue;
                }
                $plugins[] = array(
                    'name' => $directory,
                    'description' => isset($descriptions[$directory]) ? $descriptions[$directory] : '',
                    'readme' => substr($pluginsPath . $directory . '/README.rst', strlen(PATH_site) - 1),
                    'docst3o' => in_array($directory, $availableOnDocsTypo3Org),
                );
            }
        }

        return $plugins;
    }

    /**
     * Returns true if the source files of PyYAML are available locally.
     *
     * @return bool
     */
    public static function hasPyYaml()
    {
        $sphinxSourcesPath = static::getSphinxSourcesPath();
        $setupFile = $sphinxSourcesPath . 'PyYAML/setup.py';
        return is_file($setupFile);
    }

    /**
     * Downloads the source files of PyYAML.
     *
     * @param array|null $output Log of operations
     * @return bool true if operation succeeded, otherwise false
     * @throws \Exception
     * @see http://pyyaml.org/
     */
    public static function downloadPyYaml(array &$output = null)
    {
        $success = true;
        $tempPath = MiscUtility::getTemporaryPath();
        $sphinxSourcesPath = static::getSphinxSourcesPath();

        $url = 'http://pyyaml.org/download/pyyaml/PyYAML-3.10.tar.gz';
        $archiveFilename = $tempPath . 'PyYAML-3.10.tar.gz';
        $archiveContent = MiscUtility::getUrl($url);
        if ($archiveContent && GeneralUtility::writeFile($archiveFilename, $archiveContent)) {
            $output[] = '[INFO] PyYAML 3.10 has been downloaded.';

            $targetPath = $sphinxSourcesPath . 'PyYAML';

            // Unpack PyYAML archive
            $out = array();
            if (static::unarchive($archiveFilename, $targetPath, 'PyYAML-3.10', $out)) {
                $output[] = '[INFO] PyYAML has been unpacked.';
            } else {
                $success = false;
                $output[] = '[ERROR] Could not extract PyYAML:' . LF . LF . implode($out, LF);
            }
        } else {
            $success = false;
            $output[] = '[ERROR] Could not download ' . htmlspecialchars($url);
        }

        return $success;
    }

    /**
     * Builds and installs PyYAML locally.
     *
     * @param string $sphinxVersion The Sphinx version to build PyYAML for
     * @param array|null $output Log of operations
     * @return bool true if operation succeeded, otherwise false
     * @throws \Exception
     */
    public static function buildPyYaml($sphinxVersion, array &$output = null)
    {
        $sphinxSourcesPath = static::getSphinxSourcesPath();
        $sphinxPath = static::getSphinxPath();

        $pythonHome = $sphinxPath . $sphinxVersion;
        $pythonLib = $pythonHome . '/lib/python';

        // Compatibility with Windows platform
        $pythonHome = str_replace('/', DIRECTORY_SEPARATOR, $pythonHome);
        $pythonLib = str_replace('/', DIRECTORY_SEPARATOR, $pythonLib);

        if (!is_dir($pythonLib)) {
            $success = false;
            $output[] = '[ERROR] Invalid Python library: ' . $pythonLib;
            return $success;
        }

        $setupFile = $sphinxSourcesPath . 'PyYAML' . DIRECTORY_SEPARATOR . 'setup.py';
        if (is_file($setupFile)) {
            $success = static::buildWithPython(
                'PyYAML',
                $setupFile,
                $pythonHome,
                $pythonLib,
                '',
                $output
            );
            if (!$success) {
                // Possible known problem: libyaml is not found, try to compile without it
                $output[] = '[WARNING] Could not build PyYAML, trying again without libyaml';
                $success = static::buildWithPython(
                    'PyYAML',
                    $setupFile,
                    $pythonHome,
                    $pythonLib,
                    '--without-libyaml',
                    $output
                );
            }
        } else {
            $success = false;
            $output[] = '[ERROR] Setup file ' . $setupFile . ' was not found.';
        }

        return $success;
    }

    /**
     * Returns true if the source files of Python Imaging Library are available locally.
     *
     * @return bool
     */
    public static function hasPIL()
    {
        $sphinxSourcesPath = static::getSphinxSourcesPath();
        $setupFile = $sphinxSourcesPath . 'Imaging/setup.py';
        return is_file($setupFile);
    }

    /**
     * Downloads the source files of Python Imaging Library.
     *
     * @param array|null $output Log of operations
     * @return bool true if operation succeeded, otherwise false
     * @throws \Exception
     * @see https://pypi.python.org/pypi/PIL
     */
    public static function downloadPIL(array &$output = null)
    {
        $success = true;
        $tempPath = MiscUtility::getTemporaryPath();
        $sphinxSourcesPath = static::getSphinxSourcesPath();

        $url = 'http://effbot.org/media/downloads/Imaging-1.1.7.tar.gz';
        $archiveFilename = $tempPath . 'Imaging-1.1.7.tar.gz';
        $archiveContent = MiscUtility::getUrl($url);
        if ($archiveContent && GeneralUtility::writeFile($archiveFilename, $archiveContent)) {
            $output[] = '[INFO] Python Imaging Library 1.1.7 has been downloaded.';

            $targetPath = $sphinxSourcesPath . 'Imaging';

            // Unpack Python Imaging Library archive
            $out = array();
            if (static::unarchive($archiveFilename, $targetPath, 'Imaging-1.1.7', $out)) {
                $output[] = '[INFO] Python Imaging Library has been unpacked.';
            } else {
                $success = false;
                $output[] = '[ERROR] Unknown structure in archive ' . $archiveFilename;
            }
        } else {
            $success = false;
            $output[] = '[ERROR] Could not download ' . htmlspecialchars($url);
        }

        return $success;
    }

    /**
     * Builds and installs Python Imaging Library locally.
     *
     * @param string $sphinxVersion The Sphinx version to build Python Imaging Library for
     * @param array|null $output Log of operations
     * @return bool true if operation succeeded, otherwise false
     * @throws \Exception
     */
    public static function buildPIL($sphinxVersion, array &$output = null)
    {
        $sphinxSourcesPath = static::getSphinxSourcesPath();
        $sphinxPath = static::getSphinxPath();

        $pythonHome = $sphinxPath . $sphinxVersion;
        $pythonLib = $pythonHome . '/lib/python';

        // Compatibility with Windows platform
        $pythonHome = str_replace('/', DIRECTORY_SEPARATOR, $pythonHome);
        $pythonLib = str_replace('/', DIRECTORY_SEPARATOR, $pythonLib);

        if (!is_dir($pythonLib)) {
            $success = false;
            $output[] = '[ERROR] Invalid Python library: ' . $pythonLib;
            return $success;
        }

        $setupFile = $sphinxSourcesPath . 'Imaging' . DIRECTORY_SEPARATOR . 'setup.py';
        if (is_file($setupFile)) {
            $success = static::buildWithPython(
                'Python Imaging Library',
                $setupFile,
                $pythonHome,
                $pythonLib,
                '',
                $output
            );
        } else {
            $success = false;
            $output[] = '[ERROR] Setup file ' . $setupFile . ' was not found.';
        }

        return $success;
    }

    /**
     * Returns true if the source files of Pygments are available locally.
     *
     * @param string $sphinxVersion The Sphinx version to build Pygments for
     * @return bool
     */
    public static function hasPygments($sphinxVersion)
    {
        $sphinxSourcesPath = static::getSphinxSourcesPath();
        $minimumPygmentsVersion = static::getMinimumLibraryVersion('Pygments', $sphinxSourcesPath . $sphinxVersion);

        $present = false;
        $highestVersion = $minimumPygmentsVersion;

        $localPygmentsVersions = static::getPygmentsLocalVersions();
        foreach ($localPygmentsVersions as $version) {
            if (version_compare($version, $highestVersion, '>=')) {
                $present = true;
                $highestVersion = $version;
            }
        }

        // If we are online, try to see if a newer version is available. If so,
        // we need to use it otherwise it will be fetched automatically for us
        // and the newer version will override our own patched version (to
        // include TypoScript support)
        if ($present) {
            $availableVersions = static::getPygmentsAvailableVersions();
            foreach ($availableVersions as $version => $info) {
                if (version_compare($version, $highestVersion, '>')) {
                    // At least one newer version is available online
                    return false;
                }
            }
        }

        return $present;
    }

    /**
     * Downloads the source files of Pygments.
     *
     * @param string $sphinxVersion The Sphinx version to build Pygments for
     * @param array|null $output Log of operations
     * @return bool true if operation succeeded, otherwise false
     * @throws \Exception
     * @see http://pygments.org/
     */
    public static function downloadPygments($sphinxVersion, array &$output = null)
    {
        $success = true;
        $tempPath = MiscUtility::getTemporaryPath();
        $sphinxSourcesPath = static::getSphinxSourcesPath();

        $versionUrl = static::getPygmentsVersionUrl($sphinxVersion);
        if ($versionUrl === null) {
            $output[] = '[ERROR] Could not find a compatible version of Pygments';
            return false;
        }

        $url = $versionUrl['url'];
        $archiveFilename = $tempPath . basename($url);
        $archiveContent = MiscUtility::getUrl($url);
        if ($archiveContent && GeneralUtility::writeFile($archiveFilename, $archiveContent)) {
            $output[] = '[INFO] Pygments ' . $versionUrl['version'] . ' has been downloaded.';

            $targetPath = $sphinxSourcesPath . 'Pygments/' . $versionUrl['version'];

            // Unpack Pygments archive
            $out = array();
            if (static::unarchive($archiveFilename, $targetPath, 'birkenfeld-pygments-main-', $out)) {
                $output[] = '[INFO] Pygments ' . $versionUrl['version'] . ' has been unpacked.';
            } else {
                $success = false;
                $output[] = '[ERROR] Unknown structure in archive ' . $archiveFilename;
            }
        } else {
            $success = false;
            $output[] = '[ERROR] Could not download ' . htmlspecialchars($url);
        }

        return $success;
    }

    /**
     * Builds and installs Pygments locally.
     *
     * @param string $sphinxVersion The Sphinx version to build Pygments for
     * @param array|null $output Log of operations
     * @return bool true if operation succeeded, otherwise false
     * @throws \Exception
     */
    public static function buildPygments($sphinxVersion, array &$output = null)
    {
        $sphinxSourcesPath = static::getSphinxSourcesPath();
        $sphinxPath = static::getSphinxPath();

        $pythonHome = $sphinxPath . $sphinxVersion;
        $pythonLib = $pythonHome . '/lib/python';

        // Compatibility with Windows platform
        $pythonHome = str_replace('/', DIRECTORY_SEPARATOR, $pythonHome);
        $pythonLib = str_replace('/', DIRECTORY_SEPARATOR, $pythonLib);

        if (!is_dir($pythonLib)) {
            $success = false;
            $output[] = '[ERROR] Invalid Python library: ' . $pythonLib;
            return $success;
        }

        $minimumPygmentsVersion = static::getMinimumLibraryVersion('Pygments', $sphinxSourcesPath . $sphinxVersion);

        $highestVersion = null;
        $localPygmentsVersions = static::getPygmentsLocalVersions();
        foreach ($localPygmentsVersions as $version) {
            if (version_compare($version, $highestVersion, '>')) {
                $highestVersion = $version;
            }
        }

        $setupFile = $sphinxSourcesPath . 'Pygments' . DIRECTORY_SEPARATOR . $highestVersion . DIRECTORY_SEPARATOR . 'setup.py';
        if (is_file($setupFile)) {
            static::configureTyposcriptForPygments($highestVersion, $output);

            $success = static::buildWithPython(
                'Pygments',
                $setupFile,
                $pythonHome,
                $pythonLib,
                '',
                $output
            );
        } else {
            $success = false;
            $output[] = '[ERROR] Setup file ' . $setupFile . ' was not found.';
        }

        return $success;
    }

    /**
     * Configures TypoScript support for Pygments.
     *
     * @param string $pygmentsVersion
     * @param array|null $output Log of operations
     * @return void
     */
    private static function configureTyposcriptForPygments($pygmentsVersion, array &$output = null)
    {
        $sphinxSourcesPath = static::getSphinxSourcesPath();
        $lexersPath = $sphinxSourcesPath . 'Pygments' . DIRECTORY_SEPARATOR . $pygmentsVersion . DIRECTORY_SEPARATOR . 'pygments' . DIRECTORY_SEPARATOR . 'lexers' . DIRECTORY_SEPARATOR;

        $url = 'https://raw.githubusercontent.com/Tuurlijk/Pygments-TypoScript-Lexer/master/typoscript.py';
        $libraryFilename = $lexersPath . 'typoscript.py';
        $libraryContent = MiscUtility::getUrl($url);

        if ($libraryContent) {
            if (!is_file($libraryFilename) || md5_file($libraryFilename) !== md5($libraryContent)) {
                if (GeneralUtility::writeFile($libraryFilename, $libraryContent)) {
                    $output[] = '[OK] TypoScript library for Pygments ' . $pygmentsVersion . ' successfully downloaded/updated.';
                }
            }
            if (is_file($libraryFilename)) {
                // Update the list of Pygments lexers
                $python = escapeshellarg(CommandUtility::getCommand('python'));
                $cmd = 'cd ' . escapeshellarg($lexersPath) . ' && ' .
                    $python . ' _mapping.py 2>&1';
                $out = array();
                static::exec($cmd, $out, $ret);
                if ($ret === 0) {
                    $output[] = '[OK] TypoScript library successfully registered with Pygments ' . $pygmentsVersion . '.';
                } else {
                    $output[] = '[WARNING] Could not install TypoScript library for Pygments ' . $pygmentsVersion . '.';
                }
            }
        }
    }

    /**
     * Returns true if the source files of the latex.typo3 package are available locally.
     *
     * @return bool
     */
    public static function hasLaTeXPackage()
    {
        $sphinxSourcePath = static::getSphinxSourcesPath();
        $packageFile = $sphinxSourcePath . 'latex.typo3/typo3.sty';
        return is_file($packageFile);
    }

    /**
     * Downloads the source files of the latex.typo3 package.
     *
     * @param array|null $output
     * @return bool true if operation succeeded, otherwise false
     * @throws \Exception
     */
    public static function downloadLaTeXPackage(array &$output = null)
    {
        $success = static::cloneGitHubProject('https://github.com/TYPO3-Documentation/latex.typo3.git', $output, 'latex.typo3');
        return $success;
    }

    /**
     * Returns true if the source files of rst2pdf are available locally.
     *
     * @return bool
     */
    public static function hasRst2Pdf()
    {
        $sphinxSourcesPath = static::getSphinxSourcesPath();
        $setupFile = $sphinxSourcesPath . 'rst2pdf/setup.py';
        return is_file($setupFile);
    }

    /**
     * Downloads the source files of rst2pdf.
     *
     * @param array|null $output Log of operations
     * @return bool true if operation succeeded, otherwise false
     * @throws \Exception
     * @see http://rst2pdf.ralsina.me/
     */
    public static function downloadRst2Pdf(array &$output = null)
    {
        $success = true;
        $tempPath = MiscUtility::getTemporaryPath();
        $sphinxSourcesPath = static::getSphinxSourcesPath();

        $url = 'https://github.com/rst2pdf/rst2pdf/archive/0.93.tar.gz';
        $archiveFilename = $tempPath . 'rst2pdf-0.93.tar.gz';
        $archiveContent = MiscUtility::getUrl($url);
        if ($archiveContent && GeneralUtility::writeFile($archiveFilename, $archiveContent)) {
            $output[] = '[INFO] rst2pdf 0.93 has been downloaded.';

            $targetPath = $sphinxSourcesPath . 'rst2pdf';

            // Unpack rst2pdf archive
            $out = array();
            if (static::unarchive($archiveFilename, $targetPath, 'rst2pdf-0.93', $out)) {
                $output[] = '[INFO] rst2pdf has been unpacked.';
            } else {
                $success = false;
                $output[] = '[ERROR] Could not extract rst2pdf:' . LF . LF . implode($out, LF);
            }
        } else {
            $success = false;
            $output[] = '[ERROR] Could not download ' . htmlspecialchars($url);
        }

        return $success;
    }

    /**
     * Builds and installs rst2pdf locally.
     *
     * @param string $sphinxVersion The Sphinx version to build rst2pdf for
     * @param array|null $output Log of operations
     * @return bool true if operation succeeded, otherwise false
     * @throws \Exception
     */
    public static function buildRst2Pdf($sphinxVersion, array &$output = null)
    {
        $sphinxSourcesPath = static::getSphinxSourcesPath();
        $sphinxPath = static::getSphinxPath();

        $pythonHome = $sphinxPath . $sphinxVersion;
        $pythonLib = $pythonHome . '/lib/python';

        // Compatibility with Windows platform
        $pythonHome = str_replace('/', DIRECTORY_SEPARATOR, $pythonHome);
        $pythonLib = str_replace('/', DIRECTORY_SEPARATOR, $pythonLib);

        if (!is_dir($pythonLib)) {
            $success = false;
            $output[] = '[ERROR] Invalid Python library: ' . $pythonLib;
            return $success;
        }

        $setupFile = $sphinxSourcesPath . 'rst2pdf' . DIRECTORY_SEPARATOR . 'setup.py';
        if (is_file($setupFile)) {
            $success = static::buildWithPython(
                'rst2pdf',
                $setupFile,
                $pythonHome,
                $pythonLib,
                '',
                $output
            );
        } else {
            $success = false;
            $output[] = '[ERROR] Setup file ' . $setupFile . ' was not found.';
        }

        return $success;
    }

    /**
     * Returns true if a given Python library is present (installed).
     *
     * @param string $library Name of the library (without version)
     * @param string $sphinxVersion The Sphinx version to check for
     * @return bool
     */
    public static function hasLibrary($library, $sphinxVersion)
    {
        $sphinxPath = static::getSphinxPath();
        $pythonHome = $sphinxPath . $sphinxVersion;
        $pythonLib = $pythonHome . '/lib/python';

        $directories = GeneralUtility::get_dirs($pythonLib);
        foreach ($directories as $directory) {
            if (GeneralUtility::isFirstPartOfStr($directory, $library . '-')) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns a list of online available versions of Sphinx.
     * Please note: all versions older than 1.0 are automatically discarded
     * as they are most probably of absolutely no use.
     *
     * @return array
     */
    public static function getSphinxAvailableVersions()
    {
        $html = MiscUtility::getUrlWithCache('https://github.com/sphinx-doc/sphinx/releases');

        $tagsHtml = substr($html, strpos($html, '<ul class="release-timeline-tags">'));
        $tagsHtml = substr($tagsHtml, 0, strpos($tagsHtml, '<div data-pjax class="paginate-container">'));

        $versions = array();
        preg_replace_callback(
            '#<div class="tag-info commit js-details-container Details">.*?<span class="tag-name">([^<]*)</span>.*?<a href="([^"]+)" rel="nofollow">.*?zip.*?</a>#s',
            function ($matches) use (&$versions) {
                if ($matches[1] !== 'tip' && version_compare($matches[1], '1.1.3', '>=')) {
                    $key = $matches[1];
                    $name = $key;
                    // Make sure main release (e.g., "1.2") gets a ".0" patch release version as well
                    if (preg_match('/^\d+\.\d+$/', $name)) {
                        $name .= '.0';
                    }
                    // Fix sorting of alpha/beta releases
                    $name = str_replace(['a', 'b'], [' alpha ', ' beta '], $name);

                    $versions[$name] = array(
                        'key' => $key,
                        'name' => $name,
                        'url' => $matches[2],
                    );
                }
            },
            $tagsHtml
        );

        krsort($versions);
        return $versions;
    }

    /**
     * Returns a list of online available versions of Pygments.
     *
     * @return array
     */
    protected static function getPygmentsAvailableVersions()
    {
        $baseUrl = 'https://bitbucket.org';
        $html = MiscUtility::getUrlWithCache($baseUrl . '/birkenfeld/pygments-main/downloads/?tab=tags');

        $tagsHtml = substr($html, strpos($html, ' id="tag-downloads"'));

        $versions = array();
        preg_replace_callback(
            '#<tr class="iterable-item">.*?<td class="name">([^<]*)</td>.*?<a class="lfs-warn-link" href="([^"]+)">gz</a>#s',
            function ($matches) use ($baseUrl, &$versions) {
                if ($matches[1] !== 'tip') {
                    $key = $matches[1];
                    $name = $key;

                    // Remove RC's
                    if (strpos($name, 'rc') === false) {
                        $versions[$name] = array(
                            'key' => $key,
                            'name' => $name,
                            'url' => $baseUrl . $matches[2],
                        );
                    }
                }
            },
            $tagsHtml
        );

        krsort($versions);
        return $versions;
    }

    /**
     * Returns the changes for a given version of Sphinx.
     *
     * @param string $sphinxVersion
     * @return string
     */
    public static function getChanges($sphinxVersion)
    {
        $html = MiscUtility::getUrlWithCache('http://www.sphinx-doc.org/en/latest/changes.html');

        // Fix name in case the human-readable version is given as parameter
        $sphinxVersion = str_replace([' alpha ', ' beta '], ['a', 'b'], $sphinxVersion);
        if (strlen($sphinxVersion) > 4 && substr($sphinxVersion, -2) === '.0') {
            $sphinxVersion = substr($sphinxVersion, 0, -2);
        }

        $releaseId = 'release-' . str_replace('.', '-', $sphinxVersion) . '-released-';

        $changesHtml = substr($html, strpos($html, '<div class="section" id="' . $releaseId));
        if (strlen($changesHtml) === strlen($html)) {
            return null;
        }

        $changesHtml = trim(substr($changesHtml, strpos($changesHtml, '>') + 1));
        if (($pos = strpos($changesHtml, '<h2>', 10)) !== false) {
            $changesHtml = substr($changesHtml, 0, $pos);
            $changesHtml = trim(substr($changesHtml, 0, strrpos($changesHtml, '</div>')));
        }

        return $changesHtml;
    }

    /**
     * Returns a list of locally available versions of Sphinx.
     *
     * @return array
     */
    public static function getSphinxLocalVersions()
    {
        $sphinxPath = static::getSphinxPath();
        $versions = array();
        if (is_dir($sphinxPath)) {
            $versions = GeneralUtility::get_dirs($sphinxPath);
        }
        return $versions;
    }

    /**
     * Returns a list of locally available versions of Pygments.
     *
     * @return array
     */
    public static function getPygmentsLocalVersions()
    {
        $versions = array();
        $sphinxSourcesPath = static::getSphinxSourcesPath();
        $pygmentsPath = $sphinxSourcesPath . 'Pygments/';

        if (is_file($pygmentsPath . 'setup.py')) {
            // Version of Pygments downloaded with EXT:sphinx <= 2.2.3
            GeneralUtility::rmdir($pygmentsPath, true);
            return $versions;
        }
        if (is_dir($pygmentsPath)) {
            $versions = GeneralUtility::get_dirs($pygmentsPath);
        }

        return $versions;
    }

    /**
     * Returns the minimum library version required for a given version of Sphinx.
     *
     * @param string $library
     * @param string $sphinxSourcesPath
     * @return string
     */
    protected static function getMinimumLibraryVersion($library, $sphinxSourcesPath)
    {
        $version = '0.0';
        $fileName = rtrim($sphinxSourcesPath, '/') . '/test-reqs.txt';
        if (!is_file($fileName)) {
            // Legacy requirement file
            $fileName = rtrim($sphinxSourcesPath, '/') . '/Sphinx.egg-info/requires.txt';
            if (!is_file($fileName)) {
                return $version;
            }
        }

        $requirements = file_get_contents($fileName);
        $lines = explode(LF, $requirements);
        foreach ($lines as $line) {
            list($l, $v) = GeneralUtility::trimExplode('>=', $line);
            if ($l === $library) {
                $version = $v;
                break;
            }
        }

        return $version;
    }

    /**
     * Returns the version and download url of a version of Pygments
     * compatible with a given version of Sphinx, looking for the highest
     * version available.
     *
     * @param string $sphinxVersion
     * @return array|null ['version' => <version>, 'url' => <downloadUrl>]
     */
    protected static function getPygmentsVersionUrl($sphinxVersion)
    {
        $sphinxSourcesPath = static::getSphinxSourcesPath();

        $minimumVersion = static::getMinimumLibraryVersion('Pygments', $sphinxSourcesPath . $sphinxVersion);
        if ($minimumVersion === '0.0') {
            // Should never happen
            $minimumVersion = '1.0';
        }

        $highestVersion = null;
        $availableVersions = static::getPygmentsAvailableVersions();
        foreach ($availableVersions as $version => $info) {
            if (version_compare($version, $highestVersion, '>')) {
                $highestVersion = $version;
            }
        }

        return $highestVersion !== null
            ? array('version' => $highestVersion, 'url' => $availableVersions[$highestVersion]['url'])
            : null;
    }

    /**
     * Logs and executes a command.
     *
     * @param string $cmd Command to be executed
     * @param array|null $output Log of operations
     * @param integer $returnValue Return code
     * @return array|null Last line of the shell output
     */
    protected static function exec($cmd, &$output = null, &$returnValue = 0)
    {
        static::$log[] = '[CMD] ' . $cmd;
        $lastLine = CommandUtility::exec($cmd, $out, $returnValue);
        static::$log = array_merge(static::$log, $out);
        $output = $out;
        return $lastLine;
    }

    /**
     * Untars/Unzips an archive into a given target directory.
     *
     * @param string $archiveFilename Absolute path to the zip or tar.gz archive
     * @param string $targetDirectory Absolute path to the target directory
     * @param string|null $moveContentOutsideOfDirectoryPrefix Directory prefix to remove
     * @param array|null $output Log of operations
     * @return bool true if operation succeeded, otherwise false
     */
    public static function unarchive($archiveFilename, $targetDirectory, $moveContentOutsideOfDirectoryPrefix = null, array &$output = null)
    {
        $success = false;

        static::$log[] = '[INFO] Recreating directory ' . $targetDirectory;
        GeneralUtility::rmdir($targetDirectory, true);
        GeneralUtility::mkdir_deep($targetDirectory . DIRECTORY_SEPARATOR);

        if (substr($archiveFilename, -4) === '.zip') {
            $unzip = escapeshellarg(CommandUtility::getCommand('unzip'));
            $cmd = $unzip . ' ' . escapeshellarg($archiveFilename) . ' -d ' . escapeshellarg($targetDirectory) . ' 2>&1';
            static::exec($cmd, $output, $ret);
        } else {
            if (CommandUtility::checkCommand('tar')) {
                $tar = escapeshellarg(CommandUtility::getCommand('tar'));
                $cmd = $tar . ' xzvf ' . escapeshellarg($archiveFilename) . ' -C ' . escapeshellarg($targetDirectory) . ' 2>&1';
                static::exec($cmd, $output, $ret);
            } else {
                // Fallback method
                try {
                    // Remove similar .tar archives (possible garbage from previous run)
                    $tarFilePattern = PathUtility::dirname($archiveFilename) . DIRECTORY_SEPARATOR;
                    $tarFilePattern .= preg_replace('/(-[0-9.]+)?\.tar\.gz$/', '*.tar', PathUtility::basename($archiveFilename));
                    $files = glob($tarFilePattern);
                    if ($files === false) {
                        // An error occured
                        $files = array();
                    }
                    foreach ($files as $file) {
                        @unlink($file);
                    }
                    // Decompress from .gz
                    $p = new \PharData($archiveFilename);
                    $phar = $p->decompress();
                    $phar->extractTo($targetDirectory);
                    // Remove garbage
                    $files = glob($tarFilePattern);
                    foreach ($files as $file) {
                        @unlink($file);
                    }
                    $ret = 0;
                } catch (\Exception $e) {
                    $output[] = $e->getMessage();
                    $ret = 1;
                }
            }
        }
        if ($ret === 0) {
            $success = true;
            if ($moveContentOutsideOfDirectoryPrefix !== null) {
                // When unpacking the sources, content is located under a directory
                $directories = GeneralUtility::get_dirs($targetDirectory);
                if (GeneralUtility::isFirstPartOfStr($directories[0], $moveContentOutsideOfDirectoryPrefix)) {
                    $fromDirectory = $targetDirectory . DIRECTORY_SEPARATOR . $directories[0];
                    MiscUtility::recursiveCopy($fromDirectory, $targetDirectory);
                    GeneralUtility::rmdir($fromDirectory, true);

                    // Remove tar.gz archive as we don't need it anymore
                    @unlink($archiveFilename);
                } else {
                    $success = false;
                }
            }
        }

        return $success;
    }

    /**
     * Builds a library with Python.
     *
     * @param string $name Name of the library
     * @param string $setupFile Absolute path to the setup file
     * @param string $pythonHome Absolute path to Python HOME
     * @param string $pythonLib Absolute path to Python libraries
     * @param string $extraFlags Optional extra compilation flags
     * @param array|null $output Log of operations
     * @return bool true if operation succeeded, otherwise false
     */
    protected static function buildWithPython($name, $setupFile, $pythonHome, $pythonLib, $extraFlags = '', array &$output = null)
    {
        $export = '';
        $clientInfo = GeneralUtility::clientInfo();
        if ($clientInfo['SYSTEM'] === 'mac') {
            // See https://forge.typo3.org/issues/58424
            $export = 'ARCHFLAGS=-Wno-error=unused-command-line-argument-hard-error-in-future ';
        }

        $python = $export . escapeshellarg(CommandUtility::getCommand('python'));
        $cmd = 'cd ' . escapeshellarg(PathUtility::dirname($setupFile)) . ' && ' .
            $python . ' setup.py clean 2>&1 && ' .
            $python . ' setup.py' . ($extraFlags ? ' ' . $extraFlags : '') . ' build 2>&1';
        $out = array();
        static::exec($cmd, $out, $ret);
        if ($ret === 0) {
            $safePythonLib = strpos($pythonLib, ' ') !== false
                ? escapeshellarg($pythonLib)
                : $pythonLib;
            $cmd = 'cd ' . escapeshellarg(PathUtility::dirname($setupFile)) . ' && ' .
                MiscUtility::getExportCommand('PYTHONPATH', $safePythonLib) . ' && ' .
                $python . ' setup.py' . ($extraFlags ? ' ' . $extraFlags : '') . ' install --home=' . escapeshellarg($pythonHome) . ' 2>&1';
            $out = array();
            static::exec($cmd, $out, $ret);
            if ($ret === 0) {
                $success = true;
                $output[] = '[OK] ' . $name . ' successfully installed.';
            } else {
                $success = false;
                $output[] = '[ERROR] Could not install ' . $name . ':' . LF . LF . implode($out, LF);
            }
        } else {
            $success = false;
            $output[] = '[WARNING] Could not build ' . $name . ':' . LF . LF . implode($out, LF);
        }

        return $success;
    }

    /**
     * Clones from Git and falls back to downloading a snapshot if it fails.
     *
     * @param string $url
     * @param array|null $output
     * @param string $projectName
     * @return bool true if operation succeeded, otherwise false
     * @throws \Exception
     */
    protected static function cloneGitHubProject($url, array &$output = null, $projectName)
    {
        $success = true;
        $tempPath = MiscUtility::getTemporaryPath();
        $sphinxSourcesPath = static::getSphinxSourcesPath();
        if (preg_match('#^https://github.com/([^/]+)/([^/]+)\.git$#', $url, $matches)) {
            $package = $matches[2];
        } else {
            $success = false;
            $output[] = '[ERROR] Invalid GitHub URL "' . $url . '"';
            return $success;
        }

        // Try to clone from Git before falling back to downloading a snapshot
        if (GitUtility::isAvailable()) {
            static::$log[] = '[INFO] Cloning ' . $url;
            if (GitUtility::cloneRepository($url, $sphinxSourcesPath)) {
                $output[] = '[INFO] Package ' . $projectName . ' has been cloned.';
                return $success;
            } else {
                $output[] = '[WARNING] Failed to clone ' . $projectName . ', will use a snapshot.';
                if (is_dir($sphinxSourcesPath . $package)) {
                    GeneralUtility::rmdir($sphinxSourcesPath . $package, true);
                }
            }
        }

        $zipFilename = $tempPath . $package . '.zip';
        $url = preg_replace('#https://github.com/(.*)\.git$#', 'https://codeload.github.com/\1/zip/master', $url);
        static::$log[] = '[INFO] Fetching ' . $url;
        $zipContent = MiscUtility::getUrl($url);
        if ($zipContent && GeneralUtility::writeFile($zipFilename, $zipContent)) {
            $output[] = '[INFO] ' . $package . ' has been downloaded.';
            $targetPath = $sphinxSourcesPath . $package;

            // Unzip the archive
            $out = array();
            if (static::unarchive($zipFilename, $targetPath, $package . '-master')) {
                $output[] = '[INFO] ' . $package . ' has been unpacked.';
            } else {
                $success = false;
                $output[] = '[ERROR] Could not extract ' . $package . ':' . LF . LF . implode($out, LF);
            }
        } else {
            $success = false;
            $output[] = '[ERROR] Cannot fetch file ' . $url . '.';
        }

        return $success;
    }

    /**
     * Clears the log of operations.
     *
     * @return void
     */
    public static function clearLog()
    {
        static::$log = array();
    }

    /**
     * Dumps the log of operations.
     *
     * @param string $filename If empty, will return the complete log of operations instead of writing it to a file
     * @return void|string
     */
    public static function dumpLog($filename = '')
    {
        $content = implode(LF, static::$log);
        if ($filename) {
            $directory = PathUtility::dirname($filename);
            GeneralUtility::mkdir($directory);
            GeneralUtility::writeFile($filename, $content);
        } else {
            return $content;
        }
    }

    /**
     * Returns the path to Sphinx sources base directory.
     *
     * @return string Absolute path to the Sphinx sources
     */
    private static function getSphinxSourcesPath()
    {
        $sphinxSourcesPath = GeneralUtility::getFileAbsFileName('uploads/tx_sphinx/');
        // Compatibility with Windows platform
        $sphinxSourcesPath = str_replace('/', DIRECTORY_SEPARATOR, $sphinxSourcesPath);

        return $sphinxSourcesPath;
    }

    /**
     * Returns the path to Sphinx binaries.
     *
     * @return string Absolute path to the Sphinx binaries
     */
    private static function getSphinxPath()
    {
        $sphinxPath = GeneralUtility::getFileAbsFileName('typo3temp/tx_sphinx/sphinx-doc/');
        // Compatibility with Windows platform
        $sphinxPath = str_replace('/', DIRECTORY_SEPARATOR, $sphinxPath);

        return $sphinxPath;
    }

}
