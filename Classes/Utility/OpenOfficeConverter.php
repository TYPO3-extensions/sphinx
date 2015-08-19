<?php
namespace Causal\Sphinx\Utility;

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
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * OpenOffice converter.
 *
 * @category    Utility
 * @package     TYPO3
 * @subpackage  tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class OpenOfficeConverter
{

    const PARAGRAPH_WIDTH = 100;

    const FOOTNOTES_DELIMITER = '-- FOOTNOTES GO HERE --';

    const EMPTY_CODE_LINE = '\\ -- EMPTY CODE LINE --';

    /**
     * Indent to use, usally 4 blank spaces or a single tab
     *
     * @var string
     */
    protected $blockIndent = '    ';

    /**
     * Path to the Sphinx project (usually the path to some "Documentation/" directory)
     *
     * @var string
     */
    protected $projectPath;

    /**
     * Metadata of the converted document
     *
     * @var array
     */
    protected $metadata;

    /**
     * Mapping of original image file names (in the OpenOffice document)
     * with prettier ones we use in Sphinx
     *
     * @var array
     */
    protected $images;

    /**
     * Mapping of style names for bold/italic/blockquote
     *
     * @var array
     */
    protected $styles;

    /**
     * Mapping of style names used for lists (bullet/number)
     *
     * @var array
     */
    protected $listStyles;

    /**
     * Array (of array) of reStructuredText files composing the
     * manual with the depth as key
     *
     * @array
     */
    protected $restFiles;

    /**
     * Array of generated anchors
     *
     * @var array
     */
    protected $anchors;

    /**
     * Stack of anchor names for current chapter/section/...
     *
     * @var array
     */
    protected $anchorStack;

    /**
     * Sets the block indent to use (usually 4 blank tabs or a single tab.
     *
     * @param string $blockIndent
     * @return void
     */
    public function setBlockIndent($blockIndent)
    {
        $this->blockIndent = $blockIndent ?: '    ';
    }

    /**
     * Converts an OpenOffice 1.x (*.sxw) document to Sphinx.
     *
     * Output directory should either not exist (will be created automatically) or
     * be empty.
     *
     * @param string $fileName OpenOffice document to convert
     * @param string $outputDirectory Path to the (usually) Documentation/ directory
     * @param string $extensionKey [optional] Extension key of the corresponding manual to be converted
     * @return void
     * @throws \RuntimeException
     */
    public function convert($fileName, $outputDirectory, $extensionKey = '')
    {
        if (!\TYPO3\CMS\Core\Utility\CommandUtility::checkCommand('unzip')) {
            throw new \RuntimeException('Unzip cannot be executed. Hint: You probably should double-check ' .
                '$TYPO3_CONF_VARS[\'SYS\'][\'binPath\'] and/or $TYPO3_CONF_VARS[\'SYS\'][\'binSetup\'].', 1375443057);
        }
        $extension = substr(PathUtility::basename($fileName), -4);
        if ($extension !== '.sxw') {
            throw new \RuntimeException('Can only convert OpenOffice 1.x (*.sxw) documents.', 1439111370);
        }
        if (!is_dir($outputDirectory)) {
            GeneralUtility::mkdir_deep($outputDirectory);
        }

        $outputDirectory = rtrim($outputDirectory, '/') . '/';
        $oooDirectory = $outputDirectory . '___ooo___/';
        $this->projectPath = $outputDirectory;

        // Unarchive the sxw document since this is actually a zip
        $zipFileName = $outputDirectory . 'manual.zip';
        copy($fileName, $zipFileName);
        GeneralUtility::rmdir($oooDirectory, true);
        GeneralUtility::mkdir($oooDirectory);
        \Causal\Sphinx\Utility\Setup::unarchive($zipFileName, $oooDirectory);
        @unlink($zipFileName);

        // Copy standard file Includes.txt
        $includesFileName = ExtensionManagementUtility::extPath('sphinx') . 'Resources/Private/Templates/Projects/TYPO3DocProject/Includes.txt';
        copy($includesFileName, $outputDirectory . 'Includes.txt');

        // Extract the OpenOffice document metadata (title/author/copyright/...)
        $this->metadata = $this->extractMetadata($oooDirectory);
        if (!empty($extensionKey)) {
            $this->metadata['extensionKey'] = $extensionKey;
            if (ExtensionManagementUtility::isLoaded($extensionKey)) {
                $extensionPath = ExtensionManagementUtility::extPath($extensionKey);
            } else {
                $extensionPath = PATH_site . 'typo3conf/ext/' . $extensionKey . '/';
            }
            if (is_file($extensionPath . 'ext_emconf.php')) {
                $EM_CONF = array();
                $_EXTKEY = $extensionKey;
                include($extensionPath . 'ext_emconf.php');
                if (!empty($EM_CONF[$_EXTKEY]['description'])) {
                    $this->metadata['description'] = $EM_CONF[$_EXTKEY]['description'];
                }
                if (!empty($EM_CONF[$_EXTKEY]['version'])) {
                    $this->metadata['version'] = $EM_CONF[$_EXTKEY]['version'];
                }
            }
        }

        // Create file Settings.yml
        $this->createSettingsYml();

        // Copy and rename images, and keep a mapping of original and new names
        GeneralUtility::mkdir($outputDirectory . 'Images/');
        $this->images = $this->extractImages($oooDirectory, $outputDirectory . 'Images/');

        $data = file_get_contents($oooDirectory . 'content.xml');
        $this->convertFromSxw($data);

        // Remove OpenOffice files
        GeneralUtility::rmdir($oooDirectory, true);
    }

    /**
     * Converts an (unpacked) OpenOffice 1.x (*.sxw) document to a Sphinx project.
     *
     * @param string $data Contents of file content.xml from unpacked OpenOffice document
     * @return void
     */
    protected function convertFromSxw($data)
    {
        // Tables are defined as <table:table ...> nodes, which is not handy
        // to loop sequentially over document blocks since everything else is
        // defined with a "text:" namespace
        $data = str_replace(array('<table:', '</table:'), array('<text:', '</text:'), $data);

        $xml = simplexml_load_string($data);
        $document = $xml->children('office', true);

        // Get styles
        $this->styles = array(
            'Preformatted Text' => array(
                'code' => true,
            ),
        );
        foreach ($document->{'automatic-styles'}->children('style', true) as $style) {
            $children = $style->children('style', true);
            if (!isset($children->properties)) continue;

            $styleName = (string)$style->attributes('style', true)->name;
            $fontProperties = $children->properties->attributes('fo', true);

            if ((string)$fontProperties->{'font-weight'} === 'bold') {
                $marker = $this->getRandomMarker('bold');
                $this->styles[$styleName] = array(
                    str_replace('XX', 'START', $marker), str_replace('XX', 'END', $marker),
                    '**', '** '
                );
            } elseif ((string)$fontProperties->{'font-style'} === 'italic') {
                $randomMarker = $this->getRandomMarker('italic');
                $this->styles[$styleName] = array(
                    str_replace('XX', 'START', $marker), str_replace('XX', 'END', $marker),
                    '*', '* '
                );
            } elseif (isset($fontProperties->{'margin-left'})) {
                $this->styles[$styleName] = array(
                    'indent' => true,
                );
            } elseif ((string)$style->attributes('style', true)->{'parent-style-name'} === 'Preformatted Text') {
                $this->styles[$styleName] = array(
                    'code' => true,
                );
            }
        }

        // Get the list styles (1st level only); other levels (up to 9) are
        // defined as part of the 1st level style but we take for acceptable
        // for this automatic conversion that the same type of bullet/number
        // is used for children as well
        $this->listStyles = array();
        foreach ($document->{'automatic-styles'}->children('text', true) as $listStyle) {
            $styleName = (string)$listStyle->attributes('style', true)->name;
            $definition = $listStyle->children('text', true);
            $isNumbered = isset($definition->{'list-level-style-number'});
            $this->listStyles[$styleName] = $isNumbered ? '#. ' : '- ';
        }

        $this->restFiles = array(
            0 => array(
                'start' => array(
                    'parent' => '',
                    'file' => 'Index',
                ),
            ),
        );
        $this->anchors = array();
        $this->anchorStack = array();

        $buffer = array();
        $bottom = array();
        $lastChapterLevel = null;
        $skipContent = true;

        // Process the body
        foreach ($document->body->children('text', true) as $block) {
            // Accumulator for reStructuredText instructions to be added
            // after the rendered content itself
            $instructions = array();

            switch ($block->getName()) {
                case 'h':
                    $success = $this->processHeading($block, $buffer, $bottom, $lastChapterLevel, $skipContent);
                    break;
                case 'p':
                    if ($skipContent) continue 2;
                    $success = $this->processParagraph($block, $buffer, $instructions, $bottom);
                    break;
                case 'ordered-list':
                    if ($skipContent) continue 2;
                    $success = $this->processOrderedList($block, $buffer, $instructions, $bottom);
                    break;
                case 'table':
                    if ($skipContent) continue 2;
                    $success = $this->processTable($block, $buffer, $instructions, $bottom);
                    break;
                default:
                    if ($skipContent) continue 2;
                    $buffer[] = '.. NOT YET HANDLED: ' . $block->getName();
                    $success = true;
            };

            if (!$success) {
                // Skip this block, probably a useless block of contents
                continue;
            }

            if (!empty($instructions)) {
                $buffer[] = LF . implode(LF . LF, $instructions);
            }

            // End of block/paragraph
            $buffer[] = '';
        }

        // Last chapter needs to be persisted as well
        $this->writeChapter($buffer, $bottom, $lastChapterLevel);

        // Append mini-tocs in every parent document
        $this->createTablesOfContents();
    }

    /**
     * Processes a heading block.
     *
     * @param \SimpleXMLElement $block
     * @param array &$buffer
     * @param array &$bottom
     * @param int &$lastChapterLevel
     * @param bool &$skipContent
     * @return bool
     */
    protected function processHeading(\SimpleXMLElement $block, array &$buffer, array &$bottom, &$lastChapterLevel, &$skipContent)
    {
        $headlineChars = array(
            1 => '=',   // Will get overlined as well
            2 => '=',
            3 => '-',
            4 => '^',
            5 => '"',
            6 => '~',   // No, seriously?
        );

        // Real content (no more toc, ...)
        $skipContent = false;

        $level = (int)$block->attributes('text', true)->level;
        $text = trim(strip_tags($this->getInnerContent($block)));
        if (empty($text)) {
            // Either an image with a heading style or one of those funny phantom headings from OOo
            $text = '((auto-generated ' . GeneralUtility::shortMD5($block->asXML(), 5) . '))';
        }
        $underline = str_repeat($headlineChars[$level], mb_strlen($text));
        $anchorKey = preg_replace('/ +/', '-', trim(preg_replace('/[^a-z0-9 ]/', '', strtolower($text))));
        $parts = explode('-', $anchorKey);
        while (strlen($anchorKey) > 30 && count($parts) > 1) {
            array_pop($parts);
            $anchorKey = implode('-', $parts);
        }
        if (in_array($anchorKey, $this->anchors)) {
            // Same chapter name at some other place in the document, anchors must
            // remain unique in whole document
            $suffix = 1;
            while (in_array($anchorKey . '-' . $suffix, $this->anchors)) {
                $suffix++;
            }
            $anchorKey .= '-' . $suffix;
        }
        $this->anchors[] = $anchorKey;

        if ($level === 1) {
            // Add the anchor
            $buffer[] = '.. _start:';
            $buffer[] = '';
            $buffer[] = $underline;
            $skipContent = true;   // We want to skip automatically generated content (toc, ...)
        } else {
            $this->writeChapter($buffer, $bottom, $lastChapterLevel);
            $lastDepth = count($this->anchorStack);
            if ($level > $lastDepth + 2) {
                // (Semantically) invalid heading level in document, e.g., jumping from H2 to H4
                $level = $lastDepth + 2;
            }
            while (count($this->anchorStack) > $level - 2) {
                array_pop($this->anchorStack);
            }
            $this->anchorStack[] = $anchorKey;
            $chapterDirectory = '';
            foreach ($this->anchorStack as $segment) {
                $chapterDirectory .= GeneralUtility::underscoredToUpperCamelCase(str_replace('-', '_', $segment)) . '/';
            }
            $this->restFiles[$level - 1][$anchorKey] = array(
                'parent' => $level > 2
                    ? $this->anchorStack[$level - 3]
                    : 'start',
                'file' => $chapterDirectory . 'Index',
            );

            // As a matter of style, prefix headings with an additional blank line
            $buffer[] = '';

            // Add the anchor
            $buffer[] = '.. _' . implode('-', $this->anchorStack) . ':';
            $buffer[] = '';
        }
        $buffer[] = $text;
        $buffer[] = $underline;

        if ($skipContent) {
            $buffer[] = '';
            $this->generatePropertyTable($buffer);
        }

        $lastChapterLevel = $level;
        return true;
    }

    /**
     * Processes a paragraph.
     *
     * @param \SimpleXMLElement $block
     * @param array &$buffer
     * @param array &$instructions
     * @param array &$bottom
     * @param int $width If positive, wrap the text to the corresponding number of characters
     * @return bool
     */
    protected function processParagraph(\SimpleXMLElement $block, array &$buffer, array &$instructions, array &$bottom, $width = null)
    {
        if ($width === null) {
            $width = static::PARAGRAPH_WIDTH;
        }

        $paragraphStyle = (string)$block->attributes('text', true)->{'style-name'};
        $isCodeBlock = isset($this->styles[$paragraphStyle]) && $this->styles[$paragraphStyle]['code'];

        $text = $this->getInnerContent($block, $isCodeBlock);
        if (empty($text) && !$isCodeBlock) {
            return false;
        }

        $text = $this->replaceBasicFormatingAndReferences($text, $instructions, $bottom, $isCodeBlock);

        if ($isCodeBlock) {
            $buffer[] = '::' . LF . LF . $this->blockIndent . ($text ?: static::EMPTY_CODE_LINE);
        } elseif (isset($this->styles[$paragraphStyle])) {
            $style = $this->styles[$paragraphStyle];
            if ($style['indent']) {
                $lines = explode(LF, $width > 0 ? wordwrap($text, $width - 4) : $text);
                $buffer[] = $this->blockIndent . implode(LF . $this->blockIndent, $lines);
            } else {
                $text = $style[2] . $text . $style[3];
                $buffer[] = $width > 0 ? wordwrap($text, $width) : $text;
            }
        } else {
            $buffer[] = $width > 0 ? wordwrap($text, $width) : $text;
        }

        return true;
    }

    /**
     * Processes an enumerated list (either ordered or unordered).
     *
     * @param \SimpleXMLElement $block
     * @param array &$buffer
     * @param array &$instructions
     * @param array &$bottom
     * @param int $level
     * @return bool
     */
    protected function processOrderedList(\SimpleXMLElement $block, array &$buffer, array &$instructions, array &$bottom, $level = 1)
    {
        $styleName = (string)$block->attributes('text', true)->{'style-name'};
        $listType = $this->listStyles[$styleName];

        $firstIndent = str_repeat(' ', ($level - 1) * strlen($listType));
        $indent = str_repeat(' ', $level * strlen($listType));
        $unindent = false;

        foreach ($block->children('text', true) as $listItem) {
            foreach ($listItem->children('text', true) as $item) {
                switch ($item->getName()) {
                    case 'p':
                        $output = array();
                        if ($this->processParagraph($item, $output, $instructions, $bottom, static::PARAGRAPH_WIDTH - 2 * $level)) {
                            if ($unindent) {
                                $buffer[] = '';
                                $unindent = false;
                            }
                            $lines = explode(LF, implode(LF, $output));
                            $buffer[] = $firstIndent . $listType . implode(LF . $indent, $lines);
                        }
                        break;
                    case 'ordered-list':
                        $output = array();
                        // Copy the style of parent list to sub lists
                        $item->addAttribute('text:style-name', $styleName, 'http://openoffice.org/2000/text');
                        if ($this->processOrderedList($item, $output, $instructions, $bottom, $level + 1)) {
                            $lines = explode(LF, implode(LF, $output));
                            $buffer[] = '';
                            $buffer[] = implode(LF, $lines);
                            $unindent = true;
                        }
                }
            }
        }

        return true;
    }

    /**
     * Processes a table.
     *
     * @param \SimpleXMLElement $block
     * @param array &$buffer
     * @param array &$instructions
     * @param array &$bottom
     * @return bool
     */
    protected function processTable(\SimpleXMLElement $block, array &$buffer, array &$instructions, array &$bottom)
    {
        $hasHeaders = false;
        $rows = array();

        if (isset($block->{'table-header-rows'})) {
            $headerRows = $block->{'table-header-rows'}->children('text', true);
            if ($headerRows->count() > 0) {
                // Only one row supported
                $row = $this->processTableRow($headerRows[0], $instructions, $bottom);
                if (!empty($row)) {
                    $hasHeaders = true;
                    $rows[] = $row;
                }
            }
        }

        foreach ($block->{'table-row'} as $row) {
            $rows[] = $this->processTableRow($row, $instructions, $bottom);
        }

        // Loop through all cells, wrapping text (except for first column) and
        // measuring the maximum width needed to hold every cells' contents
        $columnWidths = array();
        $numberRows = count($rows);
        for ($i = 0; $i < $numberRows; $i++) {
            $c = 0;
            foreach ($rows[$i] as $column => &$cell) {
                if (!isset($columnWidths[$c])) {
                    $columnWidths[$c] = 5; // We want a mininum of 5 characters for the cell's width
                }
                if ($column === 0 && empty($cell)) {
                    // First column is empty, this is not allowed by ReStructuredText with
                    // simple table syntax. Use the trick from
                    // http://docutils.sourceforge.net/docs/ref/rst/restructuredtext.html#tables
                    $cell = '\\ ';
                }
                if ($c > 0) {
                    $cell = wordwrap($cell, 55 /* arbitrary value */);
                }
                foreach (explode(LF, $cell) as $line) {
                    $columnWidths[$c] = max($columnWidths[$c], mb_strlen($line));
                }
                $c++;
            }
        }

        // Create a simple table
        $interColumnWidth = 2;
        $numberColumns = count($columnWidths);
        $markerTable = '';
        for ($i = 0; $i < $numberColumns; $i++) {
            if ($i > 0) $markerTable .= str_repeat(' ', $interColumnWidth);
            $markerTable .= str_repeat('=', $columnWidths[$i]);
        }
        $markerRow = str_replace('=', '-', $markerTable);

        $buffer[] = $markerTable;

        for ($i = 0; $i < $numberRows; $i++) {
            if ($i > 0) {
                $buffer[] = $i === 1 && $hasHeaders ? $markerTable : $markerRow;
            }
            $buffer[] = $this->getRestTableRow($rows[$i], $columnWidths, $interColumnWidth);
        }

        $buffer[] = $markerTable;

        return true;
    }

    /**
     * Processes a table row.
     *
     * @param \SimpleXMLElement $row
     * @param array &$instructions
     * @param array &$bottom
     * @return array
     */
    protected function processTableRow(\SimpleXMLElement $row, array &$instructions, array &$bottom)
    {
        $tr = array();

        foreach ($row->children('text', true) as $cell) {
            $td = array();
            foreach ($cell->children('text', true) as $p) {
                if ($p->getName() === 'p') {
                    $output = array();
                    if ($this->processParagraph($p, $output, $instructions, $bottom, 0)) {
                        $td[] = implode(LF, $output);
                    }
                }
            }
            $tr[] = implode(LF . LF, $td);
        }

        return $tr;
    }

    /**
     * Formats cells content as ReStructuredText table row.
     *
     * @param array $cells
     * @param array $widths
     * @param int $interColumnWidth
     * @return string
     */
    protected function getRestTableRow(array $cells, array $widths, $interColumnWidth)
    {
        $height = 1;
        $numberCells = count($cells);
        for ($i = 0; $i < $numberCells; $i++) {
            $cells[$i] = explode(LF, $cells[$i]);
            $height = max($height, count($cells[$i]));
        }

        $lines = array();
        for ($i = 0; $i < $height; $i++) {
            $line = '';
            for ($j = 0; $j < $numberCells; $j++) {
                if ($j > 0) $line .= str_repeat(' ', $interColumnWidth);
                $lineLength = 0;
                if (isset($cells[$j][$i])) {
                    $cellContent = trim($cells[$j][$i]);
                    $line .= $cellContent;
                    $lineLength = mb_strlen($cellContent);
                }
                // Padding until the end of the cell
                $line .= str_repeat(' ', $widths[$j] - $lineLength);
            }
            $lines[] = rtrim($line);
        }

        return implode(LF, $lines);
    }

    /**
     * Extracts the inner content of a node, thus removing the enclosing
     * node altogether.
     *
     * @param \SimpleXMLElement $node
     * @param bool $isCodeBlock
     * @return string
     */
    protected function getInnerContent(\SimpleXMLElement $node, $isCodeBlock = false)
    {
        $type = $node->getName();
        $xml = $node->asXML();
        $text = '';

        if (preg_match('#^<text:' . $type . ' .*?>(.*)</text:' . $type . '>$#', $xml, $matches)) {
            $text = $matches[1];
        }

        if ($isCodeBlock) {
            // Best output (in PDF) when indenting code with 4 spaces and never use tabs
            $text = str_replace('<text:tab-stop/>', '    ', $text);
        }

        // Remove useless instructions
        $text = str_replace(array('<text:soft-page-break/>', '<text:tab-stop/>', '<text:s/>'), '', $text);

        // Decode HTML entities
        $text = str_replace('&apos;', '\'', htmlspecialchars_decode($text));

        return $text;
    }

    /**
     * Replaces basic formating, links and images.
     *
     * @param string $text
     * @param array &$instructions
     * @param array &$bottom
     * @param bool $isCodeBlock
     * @return string
     */
    protected function replaceBasicFormatingAndReferences($text, array &$instructions, array &$bottom, $isCodeBlock)
    {
        $styles = $this->styles;
        $images = $this->images;

        // Remove styles applying to nothing (blank space or non-breaking space)
        if (!$isCodeBlock) {
            $text = preg_replace('#\s*(' . chr(194) . chr(160) . ')+#', ' ', $text);
            $multiBlankSpaceReplacement = ' ';
        } else {
            $multiBlankSpaceReplacement = '\1';
        }
        $text = preg_replace('#<text:span text:style-name="[^"]+">(\s*)</text:span>#', $multiBlankSpaceReplacement, $text);

        if (!$isCodeBlock) {
            // Escape special characters
            $text = str_replace(array('*', '`'), array('\\*', '\\`'), $text);
        }

        // Replace links
        $text = preg_replace_callback(
            '#<text:a xlink:type="simple" xlink:href="([^"]+)".*?>(.*?)</text:a>#',
            function ($matches) {
                if ($matches[1] === $matches[2]) {
                    return $matches[1];
                } else {
                    // We use an anonymous target (with two underscores) to prevent
                    // edge-cases when $matches[2] would be the same as an explicit
                    // anchor such as:
                    //
                    // .. changelog:
                    //
                    // ChangeLog
                    // =========
                    //
                    // For more details, see the
                    // `ChangeLog <https://forge.typo3.org/projects/extension-image_autoresize/repository/entry/trunk/ChangeLog>`_
                    //
                    // where "changelog" anchor would be defined twice when not using
                    // double underscore
                    return sprintf('`%s <%s>`__', $matches[2], $matches[1]);
                }
            },
            $text
        );

        // Replace inline styles
        // Step 1: pre-processing
        $text = preg_replace_callback(
            '#<text:span text:style-name="([^"]+)">(.*?)</text:span>#',
            function ($matches) use ($styles, $isCodeBlock) {
                // Code blocks do not support styles, skip!
                if (isset($styles[$matches[1]]) && !$isCodeBlock) {
                    $matches[2] = $styles[$matches[1]][0] . $matches[2] . $styles[$matches[1]][1];
                }
                return $matches[2];
            },
            $text
        );
        // Step 2: simplification of same style style next to another
        foreach ($styles as $info) {
            if (isset($info['indent']) || isset($info['code'])) continue;
            $text = preg_replace_callback(
                '#' . $info[1] . '(\s)*' . $info[0] . '#',
                function ($matches) {
                    return !empty($matches[1]) ? ' ' : '';
                },
                $text
            );
            // Styles in ReStructuredText should not have spaces at the beginning and the end
            $text = preg_replace('/' . $info[0] . '\s*/', $info[2], $text);
            $text = preg_replace('/\s*' . $info[1] . '/', $info[3], $text);
        }
        // Step 3: remove remaining styles without definition
        $text = preg_replace('#<text:(p|span) text:style-name="[^"]+">(.*?)</text:\1>#', '\2', $text);

        // Replace images
        $chapterDepth = count($this->anchorStack);
        $text = preg_replace_callback(
            '#<draw:image .*? xlink:href="([^"]+)".*?/>#',
            function ($matches) use (&$instructions, $images, $text, $chapterDepth) {
                $replacement = '___ UNKNOWN IMAGE REMOVED ___';

                if (GeneralUtility::isFirstPartOfStr($matches[1], '#Pictures/')) {
                    $matches[1] = substr($matches[1], 10);
                    if (isset($images[$matches[1]])) {
                        $image = $images[$matches[1]];
                        $imageDirectory = str_repeat('../', $chapterDepth) . 'Images/';

                        if ($matches[0] === $text) {
                            // Paragraph is just an image, include it as-this
                            $replacement = '.. image:: ' . $imageDirectory . $image;
                        } else {
                            // inline image
                            $instructions[] = '.. |' . $image . '| image:: ' . $imageDirectory . $image;
                            $replacement = '|' . $image . '| ';
                        }
                    }
                }
                return $replacement;
            },
            $text
        );

        // Replace footnotes
        $text = preg_replace_callback(
            '#<text:footnote text:id="([^"]+)".*?<text:footnote-body>(.*?)</text:footnote-body></text:footnote>#',
            function ($matches) use (&$bottom) {
                $lines = explode(LF, wordwrap($matches[2], 100));
                $bottom[] = '.. [#' . $matches[1] . '] ' . implode(LF . '   ', $lines);
                return ' [#' . $matches[1] . ']_';
            },
            $text
        );

        // Remove numbered lines
        $text = preg_replace_callback(
            '#<text:s text:c="([0-9]+)"/>(.*)#',
            function ($matches) {
                $text = $matches[2];
                $skipChars = (int)$matches[1];
                // Sometimes the colon (:) suffix is not taken into account...
                if ($text{$skipChars - 1} !== ':' && $text{$skipChars} === ':') {
                    $skipChars++;
                }
                // And sometimes this instruction just looks plain wrong since
                // no number + colon prefix is to be found
                if ($text{$skipChars - 1} !== ':') {
                    return $text;
                }
                return substr($text, $skipChars);
            },
            $text
        );

        return $text;
    }

    /**
     * Returns a random marker, to be used for multi-step replacements.
     *
     * @return string
     */
    protected function getRandomMarker($type)
    {
        static $markers = array();

        if (!isset($markers[$type])) {
            $wrap = '___';
            $markers[$type] = $wrap . md5(uniqid(rand(), true)) . '-XX' . $wrap;
        }
        return $markers[$type];
    }

    /**
     * Generates a TYPO3 property table.
     *
     * @param array &$buffer
     * @return void
     */
    protected function generatePropertyTable(array &$buffer)
    {
        $keywords = implode(', ', $this->metadata['keywords']);
        $description = !empty($this->metadata['description'])
            ? $this->metadata['description']
            : 'Description goes here...';
        $description = implode(LF . $this->blockIndent . $this->blockIndent, explode(LF, wordwrap($description, 92)));
        $language = $this->metadata['language'] ?: 'en';

        $propertyTable = <<<REST
.. only:: html

    :Classification:
        {$this->metadata['extensionKey']}

    :Version:
        |release|

    :Language:
        {$language}

    :Description:
        $description

    :Keywords:
        $keywords

    :Copyright:
        {$this->metadata['copyright']}

    :Author:
        {$this->metadata['author']}

    :Email:
        {$this->metadata['email']}

    :License:
        This document is published under the Open Publication License
        available from http://www.opencontent.org/openpub/

    :Rendered:
        |today|

    The content of this document is related to TYPO3,
    a GNU/GPL CMS/Framework available from `www.typo3.org <http://www.typo3.org/>`_.

REST;

        $buffer[] = $this->fixIndent($propertyTable);
    }

    /**
     * Creates a file Settings.yml.
     *
     * @return void
     */
    protected function createSettingsYml()
    {
        $versionParts = explode('.', $this->metadata['version']);
        $shortVersion = $versionParts[0] . '.' . $versionParts[1];

        $contents = <<<YAML
# This is the project specific Settings.yml file.
# Place Sphinx specific build information here.
# Settings given here will replace the settings of 'conf.py'.

---
conf.py:
  copyright: {$this->metadata['copyright']}
  project: {$this->metadata['title']}
  version: {$shortVersion}
  release: {$this->metadata['version']}
...
YAML;

        GeneralUtility::writeFile($this->projectPath . 'Settings.yml', $contents);
    }

    /**
     * Extracts metadata from an (unpacked) OpenOffice document.
     *
     * @param string $path
     * @return array
     */
    protected function extractMetadata($path)
    {
        $metadata = array(
            'title' => '',
            'author' => '',
            'email' => '',
            'date' => '',
            'language' => '',
            'extensionKey' => '',
            'copyright' => '',
            'keywords' => array(),
            'version' => '0.0.0',
            'oooVersion' => '0.0',
        );

        if (!is_file($path . 'meta.xml')) {
            return $metadata;
        }

        $metaContents = file_get_contents($path . 'meta.xml');
        $xml = GeneralUtility::xml2array($metaContents);

        $metadata['title'] = $xml['office:meta']['dc:title'];
        $metadata['author'] = $xml['office:meta']['dc:creator'];
        $metadata['date'] = date('d.m.Y H:i', strtotime($xml['office:meta']['dc:date']));
        $metadata['extensionKey'] = $xml['office:meta']['dc:subject'];

        $copyrightStartYear = (int)date('Y', strtotime($xml['office:meta']['meta:creation-date']));
        $copyrightEndYear = (int)date('Y', strtotime($xml['office:meta']['dc:date']));
        $metadata['copyright'] = $copyrightStartYear < $copyrightEndYear && $copyrightStartYear > 1970
            ? sprintf('%s-%s', $copyrightStartYear, $copyrightEndYear)
            : $copyrightEndYear;

        if (preg_match_all('#<meta:keyword>(.+?)</meta:keyword>#', $metaContents, $matches)) {
            $metadata['keywords'] = $matches[1];
        }

        // Try to be closer to the reality
        preg_replace_callback(
            '#<meta:user-defined meta:name="([^"]+)">(.*?)</meta:user-defined>#',
            function ($matches) use (&$metadata) {
                switch (TRUE) {
                    case $matches[1] === 'Author':
                        $metadata['author'] = $matches[2];
                        break;
                    case $matches[1] === 'Email':
                        $metadata['email'] = $matches[2];
                        break;
                    case GeneralUtility::isFirstPartOfStr(strtolower($matches[1]), 'language'):
                        $metadata['language'] = $matches[2];
                        break;
                }
            },
            $metaContents
        );

        return $metadata;
    }

    /**
     * Extracts images from an (unpacked) OpenOffice document.
     *
     * @param string $path
     * @param string $imagesPath
     * @return array
     */
    protected function extractImages($path, $imagesPath)
    {
        $images = array();
        $sourcePath = $path . 'Pictures/';
        $files = GeneralUtility::getFilesInDir($sourcePath);

        $counter = 1;
        foreach ($files as $file) {
            $extension = strtolower(substr($file, strrpos($file, '.')));
            $name = 'image-' . ($counter++) . $extension;
            copy($sourcePath . $file, $imagesPath . $name);

            $images[$file] = $name;
        }

        return $images;
    }

    /**
     * Properly indents a block of text since it is generated with
     * 4 blank spaces and this class may use another indent chunk.
     *
     * @param string $text
     * @return string
     */
    protected function fixIndent($text)
    {
        $text = str_replace('    ', $this->blockIndent, $text);
        return $text;
    }

    /**
     * Writes contents of processed chapter to disk and reinitializes
     * the buffers.
     *
     * @param array &$buffer
     * @param arra &$bottom
     * @param int $chapterLevel
     * @return void
     */
    protected function writeChapter(array &$buffer, array &$bottom, $chapterLevel)
    {
        $contents = <<<REST
.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

REST;

        $contents .= LF . '.. include:: ' . str_repeat('../', $chapterLevel - 1) . 'Includes.txt' . LF;

        // Merge adjacent code blocks in $buffer
        $numberOfLines = count($buffer);
        $withinCodeBlock = false;
        $blockPrefix = '::' . LF . LF . $this->blockIndent;
        for ($i = 0; $i < $numberOfLines; $i++) {
            if (substr($buffer[$i], 0, strlen($blockPrefix)) === $blockPrefix) {
                $contents .= LF . $buffer[$i];
                $i += 2;
                while ($i < $numberOfLines && substr($buffer[$i], 0, strlen($blockPrefix)) === $blockPrefix) {
                    $codeLine = substr($buffer[$i], strlen($blockPrefix));
                    if ($codeLine !== static::EMPTY_CODE_LINE) {
                        $contents .= LF . $this->blockIndent . $codeLine;
                    } else {
                        // Empty line in the code
                        $contents .= LF;
                    }
                    $i += 2;
                }
                $i -= 2;
            } else {
                $contents .= LF . $buffer[$i];
            }
        }

        if (!empty($bottom)) {
            $contents .= LF . '.. ' . static::FOOTNOTES_DELIMITER . LF;
            $contents .= LF . implode(LF . LF, $bottom);
        }

        $chapterKey = end(array_keys($this->restFiles[$chapterLevel - 1]));
        $chapterFileName = $this->projectPath . $this->restFiles[$chapterLevel - 1][$chapterKey]['file'] . '.rst';
        $chapterDirectory = PathUtility::dirname($chapterFileName);

        GeneralUtility::mkdir_deep($chapterDirectory);
        GeneralUtility::writeFile($chapterFileName, $contents);

        // Resets the buffers
        $buffer = array();
        $bottom = array();
    }

    /**
     * Creates the tables of contents in every parent document.
     *
     * @return void
     */
    protected function createTablesOfContents()
    {
        $data = array();
        $levels = count($this->restFiles);
        for ($i = 0; $i < $levels; $i++) {
            foreach ($this->restFiles[$i] as $anchor => $info) {
                $data[$i . '-' . $anchor] = array(
                    'file' => $info['file'],
                    'chapters' => array(),
                );
                if (!empty($info['parent'])) {
                    $parentAnchor = $info['parent'];
                    $parentLevel = $i - 1;
                    while (!isset($data[$parentLevel . '-' . $parentAnchor])) {
                        $parentLevel--;
                    }
                    $fileParts = explode('/', $info['file']);
                    $data[$parentLevel . '-' . $parentAnchor]['chapters'][] = implode('/', array_slice($fileParts, -2));
                }
            }
        }

        $i = 0;
        foreach ($data as $chapter) {
            if (empty($chapter['chapters'])) {
                continue;
            }

            $chapters = implode(LF . $this->blockIndent, $chapter['chapters']);

            if ($i === 0) {
                $tableOfContents = <<<REST

    **Table of Contents**

.. toctree::
    :maxdepth: 3
    :titlesonly:

    $chapters

REST;
            } else {
                $tableOfContents = <<<REST

.. toctree::
    :maxdepth: 2
    :titlesonly:

    $chapters

REST;
            }

            $tableOfContents = $this->fixIndent($tableOfContents);
            $fileName = $this->projectPath . $chapter['file'] . '.rst';
            $contents = file_get_contents($fileName);
            $lines = explode(LF, $contents);

            if (($index = array_search('.. ' . static::FOOTNOTES_DELIMITER, $lines)) !== false) {
                // Put the table of contents BEFORE any footnotes (because it's prettier)
                // and remove the useless delimiter at the same time
                $contents = implode(LF, array_slice($lines, 0, $index));
                $contents .= $tableOfContents . LF;
                $contents .= implode(LF, array_slice($lines, $index + 1));
            } else {
                // Put the table of contents at the end
                $contents .= $tableOfContents;
            }

            GeneralUtility::writeFile($fileName, $contents);

            $i++;
        }
    }

}
