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
 * CausalSphinx.Flashmessage.display(1, 'TYPO3 Backend - Version 4.4', 'Ready for take off');
 */
CausalSphinx.Flashmessage = function () {

    var messageContainer;
    var severities = ['notice', 'information', 'ok', 'warning', 'error', 'raw'];

    function createBox(severity, title, message) {
        if (severity == 'raw') return message;
        if (severity == 'notice' || severity == 'information') {
            severity = 'info';
            faIcon = 'fa-info';
        } else if (severity == 'ok') {
            severity = 'success';
            faIcon = 'fa-check';
        } else if (severity == 'error') {
            severity = 'danger';
            faIcon = 'fa-times';
        }
        return ['<div class="alert alert-', severity, '">',
                '<div class="media">',
                '<div class="media-left">',
                '<span class="fa-stack fa-lg">',
                '<i class="fa fa-circle fa-stack-2x"></i>',
                '<i class="fa ' + faIcon + ' fa-stack-1x"></i>',
                '</span>',
                '</div>',
                '<div class="media-body">',
                '<h4 class="alert-title">', title, '</h4>',
                '<p class="alert-message">', message, '</p>',
                '</div>'].join('');
    }

    return {
        /**
         * Shows popup
         * @member CausalSphinx.Flashmessage
         * @param int severity (0=notice, 1=information, 2=ok, 3=warning, 4=error, 5=raw)
         * @param string title
         * @param string message
         * @param float duration in sec (default 5)
         */
        display: function (severity, title, message, duration) {
            duration = duration || 5;
            if (!messageContainer) {
                messageContainer = $('#msg-div');

                // When message is clicked, hide it (only works if auto-hide is disabled)
                messageContainer.click(function () {
                    $(this).animate({top: -$(this).outerHeight()}, 500);
                });
            }

            var box = createBox(severities[severity], title, message);
            messageContainer.html(box);
            messageContainer.css('top', -messageContainer.outerHeight());

            // Slide down
            messageContainer.animate({top: "0"}, 500);

            if (duration > 0) {
                // Auto-hide (slide up) after <duration> seconds
                messageContainer.delay(duration * 1000).animate({top: -messageContainer.outerHeight()}, 500);
            }
        }
    };
}();
