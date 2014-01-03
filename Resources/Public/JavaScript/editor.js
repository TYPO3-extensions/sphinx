String.prototype.endsWith = function(suffix) {
	return this.indexOf(suffix, this.length - suffix.length) !== -1;
};

CausalSphinxEditor = {

	reference: null,
	currentReference: null,
	filename: null,
	startLine: 1,
	isDirty: false,
	isReadOnly: false,

	actions: {
		autocomplete: null,
		open: null,
		save: null,
		redirect: null,
		references: null
	},

	// CodeMirror-specific
	editor: null,

	openFile: function(file) {
		var self = CausalSphinxEditor;
		var doOpen = true;

		if (this.isDirty) {
			var NewDialog = $('<div id="MenuDialog">\
				<p>Your current document has been modified.\
					Are you sure you want to open document “' + file + '”?</p>\
			</div>');
			NewDialog.dialog({
				modal: true,
				title: 'Open File',
				show: 'clip',
				hide: 'clip',
				buttons: [
					{
						text: 'Yes',
						click: function() {
							self._openFile(file);
							$(this).dialog('close');
						}
					},
					{
						text: 'No',
						click: function() {
							$(this).dialog('close');
						}
					}
				]
			});
		} else {
			self._openFile(file);
		}
	},
	_openFile: function(file) {
		var self = CausalSphinxEditor;

		$.post(this.actions.open,
			{
				'tx_sphinx_help_sphinxdocumentation[reference]': this.reference,
				'tx_sphinx_help_sphinxdocumentation[filename]': file
			},
			function(data) {
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

	closeEditor: function() {
		var self = CausalSphinxEditor;

		if (this.isDirty) {
			var NewDialog = $('<div id="MenuDialog">\
				<p>Do you want to save the changes you made in the document “' + this.filename + '”?</p>\
			</div>');
			NewDialog.dialog({
				modal: true,
				title: 'Close Editor',
				show: 'clip',
				hide: 'clip',
				buttons: [
					{
						text: 'Yes',
						click: function() {
							self.saveAndClose();
						}
					},
					{
						text: 'No',
						click: function() {
							document.location.href = self.actions.redirect;
						}
					}
				]
			});
		} else {
			document.location.href = this.actions.redirect;
		}
	},

	save: function() {
		var self = CausalSphinxEditor;
		var contents = this.editor.getValue();

		$.post(this.actions.save,
			{
				'tx_sphinx_help_sphinxdocumentation[reference]': this.reference,
				'tx_sphinx_help_sphinxdocumentation[filename]': this.filename,
				'tx_sphinx_help_sphinxdocumentation[contents]': contents,
				'tx_sphinx_help_sphinxdocumentation[compile]': 0
			},
			function(data) {
				if (data.status == 'success') {
					self.isDirty = false;
					CausalSphinx.Flashmessage.display(2, data.statusTitle, data.statusText, 2);
				} else {
					CausalSphinx.Flashmessage.display(4, data.statusTitle, data.statusText);
				}
			}
		);
	},

	saveAndClose: function() {
		var self = CausalSphinxEditor;
		var contents = this.editor.getValue();

		$.post(this.actions.save,
			{
				'tx_sphinx_help_sphinxdocumentation[reference]': this.reference,
				'tx_sphinx_help_sphinxdocumentation[filename]': this.filename,
				'tx_sphinx_help_sphinxdocumentation[contents]': contents,
				'tx_sphinx_help_sphinxdocumentation[compile]': 1
			},
			function(data) {
				if (data.status == 'success') {
					document.location.href = self.actions.redirect;
				} else {
					CausalSphinx.Flashmessage.display(4, data.statusTitle, data.statusText);
				}
			}
		);
	},

	_initEditor: function(file) {
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

		this.editor.on("change", function(e) {
			self.isDirty = true;
		});

		// Add standard keymap for Linux/Windows
		CodeMirror.keyMap['default']['Alt-W'] = function(cm) {
			// Alt-W in case Ctrl-W below would not be catched as when using Cmd+W
			self.closeEditor();
		}
		CodeMirror.keyMap['default']['Ctrl-S'] = function(cm) {
			self.save();
		}
		CodeMirror.keyMap['default']['Shift-Ctrl-S'] = function(cm) {
			self.saveAndClose();
		}
		// Add standard keymap for Mac OS X
		CodeMirror.keyMap['default']['Ctrl-W'] = function(cm) {
			self.closeEditor();
		}
		CodeMirror.keyMap['default']['Cmd-S'] = function(cm) {
			self.save();
		}
		CodeMirror.keyMap['default']['Shift-Cmd-S'] = function(cm) {
			self.saveAndClose();
		}

		window.setTimeout(function(){
			self.editor.setCursor(self.startLine - 1, 0);
			self.editor.scrollIntoView({line: self.startLine - 1, ch: 0});
		}, 100);
		self.editor.focus();
	},

	initialize: function() {
		var self = CausalSphinxEditor;

		$("#extension-key")
			.bind('loadReferences', function(event, reference, url) {
				$.ajax({
					url: self.actions.references
						.replace(/REFERENCE/, reference)
						.replace(/URL/, url)
						.replace(/USE_PREFIX/, reference != self.currentReference ? '1' : '0')
				}).done(function(data) {
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
				select: function(event, ui) {
					if (ui.item) {
						$(this).trigger('loadReferences', [ui.item.value, ui.item.id]);
					}
				},
				change: function(event, ui) {
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

$(document).ready(function() {
	CausalSphinxEditor.initialize();
});
