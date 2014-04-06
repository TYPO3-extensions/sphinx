String.prototype.format = function () {
	var s = this,
		i = arguments.length;

	while (i--) {
		s = s.replace(new RegExp('\\{' + i + '\\}', 'gm'), arguments[i]);
	}
	return s;
};
String.prototype.endsWith = function (suffix) {
	return this.indexOf(suffix, this.length - suffix.length) !== -1;
};

CausalSphinxEditor = {

	messages: {},
	reference: null,
	currentReference: null,
	filename: null,
	startLine: 1,
	isDirty: false,
	isReadOnly: false,

	actions: {
		projectTree: null,
		autocomplete: null,
		open: null,
		save: null,
		move: null,
		rename: null,
		redirect: null,
		references: null
	},

	// CodeMirror-specific
	editor: null,

	// Spinner-specific
	spinner: null,

	openFile: function (file) {
		var self = CausalSphinxEditor;
		var doOpen = true;

		if (this.isDirty) {
			var NewDialog = $('<div id="MenuDialog"><p>' + this.messages['editor.message.open.dirty'].format(file) + '</p></div>');
			NewDialog.dialog({
				modal: true,
				title: this.messages['editor.message.open.title'],
				show: 'clip',
				hide: 'clip',
				buttons: [
					{
						text: this.messages['editor.message.yes'],
						click: function () {
							self._openFile(file);
							$(this).dialog('close');
						}
					},
					{
						text: this.messages['editor.message.no'],
						click: function () {
							$(this).dialog('close');
						}
					}
				]
			});
		} else {
			self._openFile(file);
		}
	},
	_openFile: function (file) {
		var self = CausalSphinxEditor;

		$.post(this.actions.open,
			{
				'tx_sphinx_help_sphinxdocumentation[reference]': this.reference,
				'tx_sphinx_help_sphinxdocumentation[filename]': file
			},
			function (data) {
				if (data.status == 'success') {
					self.isReadOnly = data.readOnly;
					self.editor.toTextArea();
					self.editor = null;
					self.startLine = 1;
					var textarea = document.getElementById('editor');
					textarea.value = data.contents;
					self._initEditor(file);
					self.filename = file;
					self.isDirty = false;
					$("#filename").html(file);
				} else {
					CausalSphinx.Flashmessage.display(4, data.statusTitle, data.statusText);
				}
			}
		);
	},

	moveFile: function (source, destination) {
		var ajaxData;
		$.ajax({
			type: 'POST',
			url: this.actions.move,
			async: false,
			data: {
				'tx_sphinx_help_sphinxdocumentation[reference]': this.reference,
				'tx_sphinx_help_sphinxdocumentation[source]': source,
				'tx_sphinx_help_sphinxdocumentation[destination]': destination
			},
			success: function (data) {
				ajaxData = data;
			}
		});
		return (ajaxData['status'] == 'success');
	},

	renameFile: function (file) {
		this.customDialog(
			this.actions.rename
				.replace(/FILENAME/, file),
			'editor.message.rename'
		);
	},

	loadProjectTree: function () {
		var self = CausalSphinxEditor;
		$.ajax({
			url: self.actions.projectTree
				.replace(/FILENAME/, self.filename)
		}).done(function (content) {
			$('#projectTree').html(content)
		});
	},

	customDialog: function (loadAction, saveLabelKey) {
		var self = CausalSphinxEditor;

		var ajaxData;
		$.ajax({
			url: loadAction,
			async: false,
			success: function (data) {
				ajaxData = data;
			}
		});

		if (ajaxData['status'] == 'success') {
			var formHtml = ajaxData['statusText'];
			var form;

			var NewDialog = $(formHtml).dialog({
				height: 'auto',
				width: 500,
				modal: true,
				open: function (event, ui) {
					$('.ui-state-error').hide();
					form = $('#tx-sphinx-formdialog');
				},
				buttons: [
					{
						text: this.messages[saveLabelKey],
						click: function () {
							var thisDialog = $(this);

							$.ajax({
								type: 'POST',
								url: form.prop('action'),
								data: form.serialize(),
								success: function (data) {
									if (data['status'] === 'success') {
										thisDialog.dialog('destroy');
										// TODO automatically select new name (rename)
										self.loadProjectTree();
									} else {
										$('.ui-state-error').html(data['statusText']).show();
										setTimeout(function () {
											$('.ui-state-error').fadeOut(1500);
										}, 3000);
									}
								},
							});
						}
					},
					{
						text: this.messages['editor.message.cancel'],
						click: function () {
							$(this).dialog('destroy');
						}
					}
				]
			});
		}
	},

	closeEditor: function () {
		var self = CausalSphinxEditor;

		if (this.isDirty) {
			var NewDialog = $('<div id="MenuDialog"><p>' + this.messages['editor.message.save.dirty'].format(this.filename) + '</p></div>');
			NewDialog.dialog({
				modal: true,
				title: this.messages['editor.message.close.title'],
				show: 'clip',
				hide: 'clip',
				buttons: [
					{
						text: this.messages['editor.message.yes'],
						click: function () {
							self.saveAndClose();
						}
					},
					{
						text: this.messages['editor.message.no'],
						click: function () {
							document.location.href = self.actions.redirect;
						}
					}
				]
			});
		} else {
			document.location.href = this.actions.redirect;
		}
	},

	save: function () {
		var self = CausalSphinxEditor;
		var contents = this.editor.getValue();

		this.showSpinner();
		$.post(this.actions.save,
			{
				'tx_sphinx_help_sphinxdocumentation[reference]': this.reference,
				'tx_sphinx_help_sphinxdocumentation[filename]': this.filename,
				'tx_sphinx_help_sphinxdocumentation[contents]': contents,
				'tx_sphinx_help_sphinxdocumentation[compile]': 0
			},
			function (data) {
				self.hideSpinner();
				if (data.status == 'success') {
					self.isDirty = false;
					CausalSphinx.Flashmessage.display(2, data.statusTitle, data.statusText, 2);
				} else {
					CausalSphinx.Flashmessage.display(4, data.statusTitle, data.statusText);
				}
			}
		);
	},

	saveAndClose: function () {
		var self = CausalSphinxEditor;
		var contents = this.editor.getValue();

		this.showSpinner();
		$.post(this.actions.save,
			{
				'tx_sphinx_help_sphinxdocumentation[reference]': this.reference,
				'tx_sphinx_help_sphinxdocumentation[filename]': this.filename,
				'tx_sphinx_help_sphinxdocumentation[contents]': contents,
				'tx_sphinx_help_sphinxdocumentation[compile]': 1
			},
			function (data) {
				if (data.status == 'success') {
					document.location.href = self.actions.redirect;
				} else {
					self.hideSpinner();
					CausalSphinx.Flashmessage.display(4, data.statusTitle, data.statusText);
				}
			}
		);
	},

	showSpinner: function () {
		var self = CausalSphinxEditor;

		$('<div>', {
			id: 'overlay',
			css: {
				position: 'absolute',
				top: 0,
				left: 0,
				width: '100%',
				height: '100%',
				backgroundColor: '#000',
				opacity: 0.5,
				'z-index': 10,
			}
		}).insertAfter('#editor');
		window.setTimeout(function () {
			var opts = {
				lines: 13, // The number of lines to draw
				length: 20, // The length of each line
				width: 10, // The line thickness
				radius: 30, // The radius of the inner circle
				corners: 1, // Corner roundness (0..1)
				rotate: 0, // The rotation offset
				direction: 1, // 1: clockwise, -1: counterclockwise
				color: '#fff', // #rgb or #rrggbb or array of colors
				speed: 1, // Rounds per second
				trail: 60, // Afterglow percentage
				shadow: false, // Whether to render a shadow
				hwaccel: false, // Whether to use hardware acceleration
				className: 'spinner', // The CSS class to assign to the spinner
				zIndex: 2e9, // The z-index (defaults to 2000000000)
				top: 'auto', // Top position relative to parent in px
				left: 'auto' // Left position relative to parent in px
			};
			self.spinner = new Spinner(opts).spin($('.CodeMirror')[0]);
		}, 100);
	},

	hideSpinner: function () {
		$('#overlay').remove();
		this.spinner.stop();
		this.spinner = null;
	},

	_initEditor: function (file) {
		var self = CausalSphinxEditor;
		var textarea = document.getElementById('editor');
		this.editor = CodeMirror.fromTextArea(
			textarea,
			{
				lineWrapping: true,
				readOnly: this.isReadOnly,
				lineNumbers: true,
				showTrailingSpace: true,
				mode: file.endsWith('.yml') ? 'yaml' : 'rst-base'
			}
		);
		this.editor.setSize(null, '100%');

		if (this.isReadOnly) {
			$('#editor-readonly').css('visibility', 'visible');
		} else {
			$('#editor-readonly').css('visibility', 'hidden');
		}

		this.editor.on("change", function (e) {
			self.isDirty = true;
		});

		// Keymap definitions
		CodeMirror.commands.closeEditor = function (cm) { self.closeEditor(); }
		CodeMirror.commands.save = function (cm) { self.save(); }
		CodeMirror.commands.saveAndClose = function (cm) { self.saveAndClose(); }

		// Add standard keymap for Linux/Windows
		CodeMirror.keyMap['default']['Alt-W'] = 'closeEditor';
		CodeMirror.keyMap['default']['Esc'] = 'closeEditor';
		CodeMirror.keyMap['default']['Ctrl-S'] = 'save';
		CodeMirror.keyMap['default']['Shift-Ctrl-S'] = 'saveAndClose';

		// Add standard keymap for Mac OS X
		CodeMirror.keyMap['default']['Ctrl-W'] = 'closeEditor';
		CodeMirror.keyMap['default']['Cmd-S'] = 'save';
		CodeMirror.keyMap['default']['Shift-Cmd-S'] = 'saveAndClose';

		window.setTimeout(function () {
			self.editor.setCursor(self.startLine - 1, 0);
			self.editor.scrollIntoView({line: self.startLine - 1, ch: 0});
		}, 100);
		self.editor.focus();
	},

	initialize: function () {
		var self = CausalSphinxEditor;

		$("#extension-key")
			.bind('loadReferences', function (event, reference, url) {
				$.ajax({
					url: self.actions.references
						.replace(/REFERENCE/, reference)
						.replace(/URL/, url)
						.replace(/USE_PREFIX/, reference != self.currentReference ? '1' : '0')
				}).done(function (data) {
					$('#accordion-objectsinv')
						.accordion('destroy')
						.html(data['html'])
						.accordion({ heightStyle: 'fill' });	// TODO: Problem if too many chapters (e.g., TYPO3 API)
					$('#tx-sphinx-accordion-header').html(reference);
				});
			})
			.autocomplete({
				source: CausalSphinxEditor.actions.autocomplete,
				minLength: 2,
				position: { my : "right top", at: "right bottom" },
				select: function (event, ui) {
					if (ui.item) {
						$(this).trigger('loadReferences', [ui.item.value, ui.item.id]);
					}
				},
				change: function (event, ui) {
					if ($(this).val().length == 0) {
						// Reset the references to current document
						$(this).trigger('loadReferences', self.currentReference);
					}
				}
			});

		// Initializes tooltips
		$(document).tooltip();

		this._initEditor('.rst');
	}

}

$(document).ready(function () {
	CausalSphinxEditor.initialize();
});
