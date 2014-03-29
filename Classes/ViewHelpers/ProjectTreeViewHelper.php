<?php
namespace Causal\Sphinx\ViewHelpers;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013-2014 Xavier Perseguers <xavier@causal.ch>
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

use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * Creates a project tree browser using jquery-treetable.
 *
 * @category    ViewHelpers
 * @package     tx_sphinx
 * @author      Xavier Perseguers <xavier@causal.ch>
 * @copyright   Causal Sàrl
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class ProjectTreeViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Creates a tree of directories and files for a project.
	 *
	 * @param string $projectPath The base directory of the project
	 * @param string $reveal File or folder to be initially revealed
	 * @return string
	 */
	public function render($projectPath, $reveal = '') {
		if (!empty($reveal)) {
			$reveal = md5($reveal);
		}

		$pluginId = 'tx-sphinx-projecttree';

		$out = array();
		$out[] = <<<HTML
<table id="$pluginId">
	<caption>
	  <a href="#" onclick="jQuery('#$pluginId').treetable('expandAll'); return false;">Expand all</a>
	  <a href="#" onclick="jQuery('#$pluginId').treetable('collapseAll'); return false;">Collapse all</a>
	</caption>
	<tbody>
HTML;

		/** @var \RecursiveDirectoryIterator $iterator */
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($projectPath,
				\RecursiveDirectoryIterator::SKIP_DOTS),
			\RecursiveIteratorIterator::SELF_FIRST
		);
		foreach ($iterator as $item) {
			$path = $iterator->getSubPathName();
			$identifier = md5($path);
			$trTag = '<tr data-tt-id="' . $identifier . '"';
			$trTag .= ' data-path="' . str_replace('\\', '/', $path) . '"';
			if (PathUtility::basename($path) === $path) {
				// 1st level
				$out[] = $trTag . '>';
			} else {
				$out[] = $trTag . ' data-tt-parent-id="' . md5(PathUtility::dirname($path)) . '">';
			}

			/** @var \splFileInfo $item */
			if ($item->isDir()) {
				$out[] = '<td><span class="folder">' . htmlspecialchars(PathUtility::basename($path)) . '</span></td>';
			} else {
				if (($pos = strrpos($path, '.')) !== FALSE) {
					$extension = strtolower(substr($path, $pos + 1));
				} else {
					$extension = '';
				}
				switch ($extension) {
					case 'gif':
					case 'jpg':
					case 'jpeg':
					case 'png':
						$class = 'image';
					break;
					default:
						$class = 'file';
					break;
				}
				$out[] = '<td><span class="' . $class . '">' . htmlspecialchars(PathUtility::basename($path)) . '</span></td>';
			}

			$out[] = '</tr>';
		}

		$out[] = <<<HTML
	</tbody>
</table>
HTML;

		$out[] = '<script type="text/javascript">';
		$out[] = <<<JS
$(document).ready(function() {
	$('#$pluginId').treetable({ expandable: true });

	// Highlight selected row
	$("#$pluginId tbody").on("mousedown", "tr", function() {
		$(".selected").not(this).removeClass("selected");
		$(this).toggleClass("selected");
	});

	// Open selected file on double-click
	$("#$pluginId td span[class='file']").on("dblclick", function(e) {
		var file = $(event.target).closest("tr").attr('data-path');
		CausalSphinxEditor.openFile(file);
	});

	try {
		$("#$pluginId").treetable("reveal", '$reveal');
		$("#$pluginId tr[data-tt-id='$reveal']").toggleClass("selected");
	}
	catch(error) {
		console.log(error.message);
	}
});
JS;
		$out[] = '</script>';

		return implode(LF, $out);
	}

}
