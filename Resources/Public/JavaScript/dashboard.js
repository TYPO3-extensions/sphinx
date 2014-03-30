String.prototype.format = function () {
	var s = this,
		i = arguments.length;

	while (i--) {
		s = s.replace(new RegExp('\\{' + i + '\\}', 'gm'), arguments[i]);
	}
	return s;
};

CausalSphinxDashboard = {

	messages: {},

	datatable1: null,
	datatable2: null,
	datatable3: null,

	actions: {
		addCustomProject: null,
		createCustomProject: null,
		editCustomProject: null,
		updateCustomProject: null,
		removeCustomProject: null
	},

	checkLength: function (o, min, max) {
		if (o.val().length > max || o.val().length < min) {
			o.addClass('ui-state-error');
			return false;
		} else {
			o.removeClass('ui-state-error');
			return true;
		}
	},

	addCustomProject: function () {
		var self = CausalSphinxDashboard;

		var ajaxData;
		$.ajax({
			url: self.actions.addCustomProject,
			async: false,
			success: function (data) {
				ajaxData = data;
			}
		});

		var form = ajaxData['statusText'];
		var group, name, description, documentationKey, directory;

		var NewDialog = $(form).dialog({
			height: 420,
			width: 500,
			modal: true,
			open: function (event, ui) {
				group = $('#group');
				name = $('#name');
				description = $('#description');
				documentationKey = $('#documentationKey');
				directory = $('#directory');
			},
			buttons: [
				{
					text: this.messages['dashboard.message.add'],
					click: function () {
						var bValid = true;

						// Using "&& bValid" at the end to prevent ensure
						// every self.checkLength() is called
						bValid = self.checkLength(group, 3, 50) && bValid;
						bValid = self.checkLength(name, 3, 50) && bValid;
						bValid = self.checkLength(documentationKey, 3, 50) && bValid;
						bValid = self.checkLength(directory, 10, 255) && bValid;

						if (bValid) {
							$.ajax({
								type: 'POST',
								url: self.actions.createCustomProject,
								data: {
									group: group.val(),
									name: name.val(),
									description: description.val(),
									documentationKey: documentationKey.val(),
									directory: directory.val()
								},
								success: function (data) {
									// Trick to force reload with correct active tab
									var redirectUri = document.location.href.replace(/#.*/, '#tabs-custom');
									document.location.href = redirectUri;
									location.reload(true);
								}
							});
							$(this).dialog('close');
						}
					}
				},
				{
					text: this.messages['dashboard.message.cancel'],
					click: function () {
						$(this).dialog('close');
					}
				}
			]
		});
	},

	editCustomProject: function (documentationKey) {
		var self = CausalSphinxDashboard;

		var ajaxData;
		$.ajax({
			url: self.actions.editCustomProject
				.replace(/DOCUMENTATION_KEY/, documentationKey),
			async: false,
			success: function (data) {
				ajaxData = data;
			}
		});

		if (ajaxData['status'] == 'success') {
			var form = ajaxData['statusText'];
			var group, updateGroup, name, description, documentationKey, originalDocumentationKey, directory;

			var NewDialog = $(form).dialog({
				height: 420,
				width: 500,
				modal: true,
				open: function (event, ui) {
					group = $('#group');
					updateGroup = $('#updateGroup');
					name = $('#name');
					description = $('#description');
					originalDocumentationKey = $('#originalDocumentationKey');
					documentationKey = $('#documentationKey');
					directory = $('#directory');
				},
				buttons: [
					{
						text: this.messages['dashboard.message.update'],
						click: function () {
							var bValid = true;

							// Using "&& bValid" at the end to prevent ensure
							// every self.checkLength() is called
							bValid = self.checkLength(group, 3, 50) && bValid;
							bValid = self.checkLength(name, 3, 50) && bValid;
							bValid = self.checkLength(documentationKey, 3, 50) && bValid;
							bValid = self.checkLength(directory, 10, 255) && bValid;

							if (bValid) {
								$.ajax({
									type: 'POST',
									url: self.actions.updateCustomProject,
									data: {
										group: group.val(),
										name: name.val(),
										description: description.val(),
										documentationKey: documentationKey.val(),
										originalDocumentationKey: originalDocumentationKey.val(),
										directory: directory.val(),
										updateGroup: updateGroup.prop('checked')
									},
									success: function (data) {
										// Trick to force reload with correct active tab
										var redirectUri = document.location.href.replace(/#.*/, '#tabs-custom');
										document.location.href = redirectUri;
										location.reload(true);
									}
								});
								$(this).dialog('close');
							}
						}
					},
					{
						text: this.messages['dashboard.message.cancel'],
						click: function () {
							$(this).dialog('close');
						}
					}
				]
			});
		}
	},

	removeCustomProject: function (documentationKey) {
		var self = CausalSphinxDashboard;

		var NewDialog = $('<div id="MenuDialog"><p>' + this.messages['dashboard.message.removeCustomProject'].format(documentationKey) + '</p></div>');
		NewDialog.dialog({
			modal: true,
			title: this.messages['dashboard.message.removeCustomProject.title'],
			show: 'clip',
			hide: 'clip',
			buttons: [
				{
					text: this.messages['dashboard.message.yes'],
					click: function () {
						$.ajax({
							url: self.actions.removeCustomProject
								.replace(/DOCUMENTATION_KEY/, documentationKey)
						}).done(function (data) {
							if (data.status == 'success') {
								$('#' + documentationKey.replace(/\./g, '\\.')).remove();
							}
						});
						$(this).dialog('close');
					}
				},
				{
					text: this.messages['dashboard.message.no'],
					click: function () {
						$(this).dialog('close');
					}
				}
			]
		});
	},

	// Utility method to retrieve query parameters
	getUrlVars: function getUrlVars() {
		var vars = [], hash;
		var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
		for(var i = 0; i < hashes.length; i++) {
			hash = hashes[i].split('=');
			vars.push(hash[0]);
			vars[hash[0]] = hash[1];
		}
		return vars;
	},
	// Initializes the data table, depending on the current view
	initializeView: function () {
		var getVars = this.getUrlVars();
		var tableHeight = ($(window).height() - 320) + 'px';

		this.datatable1 = $('#tx-sphinx-kickstart-list').dataTable({
			'bPaginate': false,
			'bJQueryUI': true,
			'sScrollY': tableHeight,
			'bLengthChange': false,
			'iDisplayLength': 100,
			'bStateSave': true
		});
		this.datatable2 = $('#tx-sphinx-convert-list').dataTable({
			'bPaginate': false,
			'bJQueryUI': true,
			'sScrollY': tableHeight,
			'bLengthChange': false,
			'iDisplayLength': 100,
			'bStateSave': true
		});
		this.datatable3 = $('#tx-sphinx-custom-list').dataTable({
			'fnDrawCallback': function (oSettings) {
				if (oSettings.aiDisplay.length == 0) {
					return;
				}

				var nTrs = $('#tx-sphinx-custom-list tbody tr');
				var iColspan = nTrs[0].getElementsByTagName('td').length;
				var sLastGroup = '';
				for (var i = 0; i < nTrs.length; i++) {
					var iDisplayIndex = oSettings._iDisplayStart + i;
					var sGroup = oSettings.aoData[oSettings.aiDisplay[iDisplayIndex]]._aData[0];
					if (sGroup != sLastGroup) {
						var nGroup = document.createElement('tr');
						var nCell = document.createElement('td');
						nCell.colSpan = iColspan;
						nCell.className = 'ui-state-default';
						nCell.innerHTML = sGroup;
						nGroup.appendChild(nCell);
						nTrs[i].parentNode.insertBefore(nGroup, nTrs[i]);
						sLastGroup = sGroup;
					}
				}

				oSettings.nTable.parentNode.style.height = $(oSettings.nTable).height() +'px';
			},
			'aoColumnDefs': [
				{ 'bVisible': false, 'aTargets': [0] }
			],
			'aaSortingFixed': [[ 0, 'asc' ]],
			'aaSorting': [[ 1, 'asc' ]],
			'sDom': 'lfr<t>i',
			'bJQueryUI': true,
			'sScrollY': tableHeight,
			'bLengthChange': false,
			'iDisplayLength': 100,
			'bStateSave': true
		});

		// Restore filter
		if (this.datatable1.length && getVars['search']) {
			this.datatable1.fnFilter(getVars['search']);
		}

		// Restore filter
		if (this.datatable2.length && getVars['search']) {
			this.datatable2.fnFilter(getVars['search']);
		}

		if (this.datatable3.length && getVars['search']) {
			this.datatable3.fnFilter(getVars['search']);
		}

		// Make the data table filter react to the clearing of the filter field
		$('.dataTables_wrapper .dataTables_filter input').clearable({
			onClear: function () {
				switch ($(this).closest('.dataTables_filter').attr('id')) {
					case 'tx-sphinx-kickstart-list_filter':
						CausalSphinxDashboard.datatable1.fnFilter('');
						break;
					case 'tx-sphinx-convert-list_filter':
						CausalSphinxDashboard.datatable2.fnFilter('');
						break;
					case 'tx-sphinx-custom-list_filter':
						CausalSphinxDashboard.datatable3.fnFilter('');
						break;
				}
			}
		});

		$('#tx-sphinx-custom-list_filter').after($('.dataTables_addCustomProject'));

		$('.dataTables_addCustomProject button').button({
			icons: { primary: 'ui-icon-circle-plus' }
		}).click(function () {
			CausalSphinxDashboard.addCustomProject();
		});
	}
};

// IIFE for faster access to $ and safe $ use
(function ($) {
	$(document).ready(function () {
		// Create tabs
		$('#tabs').tabs({
			'activate': function (event, ui) {
				var oTable = $('div.dataTables_scrollBody>table', ui.newPanel).dataTable();
				if (oTable.length > 0) {
					oTable.fnAdjustColumnSizing();
				}
			}
		});

		// Initialize the view
		CausalSphinxDashboard.initializeView();
	});
}(jQuery));