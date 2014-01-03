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

/**
 * Flashmessage rendered by jQuery.
 *
 * @author Xavier Perseguers <xavier@causal.ch>
 */
if (typeof CausalSphinx == 'undefined') CausalSphinx = {};

/**
 * @class CausalSphinx.Flashmessage
 * Passive popup box singleton
 * @singleton
 *
 * Example (Information message):
 * CausalSphinx.Flashmessage.display(1, 'TYPO3 Backend - Version 4.4', 'Ready for take off', 3);
 */
CausalSphinx.Flashmessage = function() {
	var messageContainer;
	var severities = ['notice', 'information', 'ok', 'warning', 'error'];

	function createBox(severity, title, message) {
		return ['<div class="typo3-message message-', severity, '">',
			'<div class="t3-icon t3-icon-actions t3-icon-actions-message t3-icon-actions-message-close t3-icon-message-' + severity + '-close"></div>',
			'<div class="header-container">',
			'<div class="message-header">', title, '</div>',
			'</div>',
			'<div class="message-body">', message, '</div>',
			'</div>'].join('');
	}

	return {
		/**
		 * Shows popup
		 * @member CausalSphinx.Flashmessage
		 * @param int severity (0=notice, 1=information, 2=ok, 3=warning, 4=error)
		 * @param string title
		 * @param string message
		 * @param float duration in sec (default 5)
		 */
		display : function(severity, title, message, duration) {
			duration = duration || 5;
			if (!messageContainer) {
				messageContainer = $('#msg-div');

				// When message is clicked, hide it (only works if auto-hide is disabled)
				messageContainer.click(function(){
					$(this).animate({top: -$(this).outerHeight()}, 500);
				});
			}

			var box = createBox(severities[severity], title, message);
			messageContainer.html(box);
			messageContainer.css('top', -messageContainer.outerHeight());

			// Slide down
			messageContainer.animate({top:"0"}, 500);

			if (duration > 0) {
				// Auto-hide (slide up) after <duration> seconds
				messageContainer.delay(duration * 1000).animate({top: -messageContainer.outerHeight()}, 500);
			}
		}
	};
}();
