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
 * CausalSphinx.Flashmessage.display(1, 'TYPO3 Backend - Version 4.4', 'Ready for take off', 3);
 */
CausalSphinx.Flashmessage = function () {

    var messageContainer;
    var severities = ['notice', 'information', 'ok', 'warning', 'error', 'raw'];

    function createBox(severity, title, message, isTYPO3v7) {
        if (severity == 'raw') return message;
        if (isTYPO3v7) {
            if (severity == 'notice' || severity == 'information') {
                severity = 'info';
            } else if (severity == 'ok') {
                severity = 'success';
            } else if (severity == 'error') {
                severity = 'danger';
            }
            return ['<div class="alert alert-', severity, '">',
                '<h4>', title, '</h4>',
                '<div class="alert-body">', message, '</div>'].join('');
        } else {
            return ['<div class="typo3-message message-', severity, '">',
                '<div class="t3-icon t3-icon-actions t3-icon-actions-message t3-icon-actions-message-close t3-icon-message-' + severity + '-close"></div>',
                '<div class="header-container">',
                '<div class="message-header">', title, '</div>',
                '</div>',
                '<div class="message-body">', message, '</div>',
                '</div>'].join('');
        }
    }

    return {

        isTYPO3v7: false,

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

            var box = createBox(severities[severity], title, message, CausalSphinx.Flashmessage.isTYPO3v7);
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
