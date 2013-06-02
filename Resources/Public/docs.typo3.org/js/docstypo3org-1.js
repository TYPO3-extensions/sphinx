/*
 * mb, 2012-12-26, 2013-02-14
 * docstypo3org-1.js
 * utf-8, äöü
 *
 * Contains the main navigation and the link for the TYPO3 logo in the upper left corner.
 */

// HTML code for the main navigation
$(document).ready(function () {
	var ulnav = ''
		+ '<ul class="nav" id="ul-nav">'
		+ '	<li><a href="http://docs.typo3.org/">Start</a>'
		+ '	</li>'
		+ '	<li><a href="/typo3cms">TYPO3 CMS</a>'
		+ '		<div class="nav-sub">'
		+ '			<div class="col">'
		+ '				<h4><a href="http://docs.typo3.org/typo3cms/References.html">References</a></h4>'
		+ '				<ul>'
		+ '					<li><a href="http://docs.typo3.org/typo3cms/CodingGuidelinesReference">    Coding Guidelines Reference      </a></li>'
		+ '					<li><a href="http://docs.typo3.org/typo3cms/CoreApiReference">             Core API Reference               </a></li>'
		+ '					<li><a href="http://docs.typo3.org/typo3cms/FileAbstractionLayerReference">File Abstraction Layer Reference </a></li>'
		+ '					<li><a href="http://docs.typo3.org/typo3cms/InsideTypo3Reference">         Inside TYPO3 Reference           </a></li>'
		+ '					<li><a href="http://docs.typo3.org/typo3cms/SkinningReference">            Skinning Reference               </a></li>'
		+ '					<li><a href="http://docs.typo3.org/typo3cms/TCAReference">                 TCA Reference                    </a></li>'
		+ '					<li><a href="http://docs.typo3.org/typo3cms/TSconfigReference">            TSconfig Reference               </a></li>'
		+ '					<li><a href="http://docs.typo3.org/typo3cms/Typo3ServicesReference">       TYPO3 Services Reference         </a></li>'
		+ '					<li><a href="http://docs.typo3.org/typo3cms/TyposcriptReference">          TypoScript Reference             </a></li>'
		+ '					<li><a href="http://docs.typo3.org/typo3cms/TyposcriptSyntaxReference">    TypoScript Syntax Reference      </a></li>'
		+ '				</ul>'
		+ '			</div>'
		+ '			<div class="col">'
		+ '				<h4><a href="http://docs.typo3.org/typo3cms/Books.html">     Books         </a></h4>'
		+ '				<ul>'
		+ '					<li><a href="http://docs.typo3.org/typo3cms/ExtbaseFluidBook/">Extbase and Fluid</a></li>'
		+ '				</ul>'
		+ '				<h4><a href="http://docs.typo3.org/typo3cms/examples.html">    Examples     </a></h4>'
		+ '				<h4><a href="http://docs.typo3.org/typo3cms/extensions/">      Extensions   </a></h4>'
		+ '				<h4><a href="http://docs.typo3.org/typo3cms/Guides.html">      Guides       </a></h4>'
		+ '				<h4><a href="http://docs.typo3.org/typo3cms/Tutorials.html">   Tutorials    </a></h4>'
		+ '				<h4><a href="http://docs.typo3.org/typo3cms/CheatSheets.html"> Cheat Sheets </a></h4>'
		+ '			</div>'
		+ '		</div>'
		+ '	</li>'
		+ '	<li><a href="http://docs.typo3.org/flow/TYPO3FlowDocumentation/">TYPO3 Flow</a>'
		+ '		<div class="nav-sub">'
		+ '			<div class="col">'
		+ '				<h4><a href="http://docs.typo3.org/flow/TYPO3FlowDocumentation/">TYPO3 Flow</a></h4>'
		+ '				<ul>'
		+ '					<li><a href="http://docs.typo3.org/flow/TYPO3FlowDocumentation/Quickstart/">           Quickstart                     </a></li>'
		+ '					<li><a href="http://docs.typo3.org/flow/TYPO3FlowDocumentation/TheDefinitiveGuide/">   The Definite Guide             </a></li>'
		+ '					<li><a href="http://docs.typo3.org/flow/TYPO3FlowDocumentation/StyleGuide/Index.html"> TYPO3 Publications Style Guide </a></li>'
		+ '				</ul>'
		+ '			</div>'
		+ '		</div>'
		+ '	</li>'
		+ '	<li><a href="http://docs.typo3.org/neos/TYPO3NeosDocumentation/">TYPO3 Neos</a>'
		+ '		<div class="nav-sub">'
		+ '			<div class="col">'
		+ '				<h4><a href="http://docs.typo3.org/neos/TYPO3NeosDocumentation/">TYPO3 Neos</a></h4>'
		+ '				<ul>'
		+ '					<li><a href="http://docs.typo3.org/neos/TYPO3NeosDocumentation/GettingStarted/">            Getting Started            </a></li>'
		+ '					<li><a href="http://docs.typo3.org/neos/TYPO3NeosDocumentation/Features/">                  Features                   </a></li>'
		+ '					<li><a href="http://docs.typo3.org/neos/TYPO3NeosDocumentation/Development/UserInterface/"> User Interface Development </a></li>'
		+ '			</div>'
		+ '		</div>'
		+ '	</li>'
		+ '</ul>'
	;
	// Insert the main navigation
	$('#ul-nav').replaceWith(ulnav);
	// Link the logo to docs.typo3.org
	$('#logo').attr('href', 'http://docs.typo3.org/');
}) ;
