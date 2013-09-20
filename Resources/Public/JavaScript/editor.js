CausalSphinxEditor = {

	reference: null,
	currentReference: null,
	filename: null,
	isDirty: false,
	isReadOnly: false,

	actions: {
		autocomplete: null,
		open: null,
		save: null,
		redirect: null,
		references: null
	},

	// Ace-specific
	editor: null,
	session: null,

	openFile: function(file) {
		var self = CausalSphinxEditor;

		if (this.isDirty) {
			var r = confirm('Your current document has been modified. Are you sure you want to open document “' + file + '”?');
			if (!r) {
				return;
			}
		}

		$.post(this.actions.open,
			{
				'tx_sphinx_help_sphinxdocumentation[reference]': this.reference,
				'tx_sphinx_help_sphinxdocumentation[filename]': file
			},
			function(data) {
				if (data.status == 'success') {
					self.isReadOnly = data.readOnly;
					self.editor.setValue(data.contents);
					self.editor.setReadOnly(self.isReadOnly);
					self.editor.gotoLine(1);
					self.editor.getSession().setScrollTop(0);
					self.filename = file;
					self.isDirty = false;
					$("#filename").html(file);
				} else {
					alert(data.statusText);
				}
			}
		);
	},

	closeEditor: function() {
		if (this.isDirty) {
			var r = confirm('Do you want to save the changes you made in the document “' + this.filename + '”?');
			if (r) {
				this.saveAndClose();
				return;
			}
		}
		document.location.href = this.actions.redirect;
	},

	save: function() {
		var self = CausalSphinxEditor;
		var contents = this.editor.getSession().getValue();

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
					alert('successful save');
				} else {
					alert(data.statusText);
				}
			}
		);
	},

	saveAndClose: function() {
		var self = CausalSphinxEditor;
		var contents = this.editor.getSession().getValue();

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
					alert(data.statusText);
				}
			}
		);
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

		// Initialize the Ace editor
		this.editor = ace.edit("editor");
		this.session = this.editor.getSession()

		this.editor.setTheme("ace/theme/github");
		this.editor.setReadOnly(this.isReadOnly);
		this.session.setMode("ace/mode/markdown");

		this.editor.on("change", function(e) {
			self.isDirty = true;
		});

		this.editor.setPrintMarginColumn(120);
		this.session.setUseWrapMode(true);
		this.session.setWrapLimitRange(120, 120);
	}

}

$(document).ready(function() {
	CausalSphinxEditor.initialize();
});
