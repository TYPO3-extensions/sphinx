CausalSphinxApplication = {
	datatable: null,
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
	initializeView: function() {
		var getVars = this.getUrlVars();

		this.datatable = $('#tx-sphinx-kickstart-list').dataTable({
			'bPaginate': false,
			'bJQueryUI': true,
			'bLengthChange': false,
			'iDisplayLength': 15,
			'bStateSave': true
		});

		// Restore filter
		if (this.datatable.length && getVars['search']) {
			this.datatable.fnFilter(getVars['search']);
		}
	}
};

// IIFE for faster access to $ and save $ use
(function ($) {
	$(document).ready(function() {
		// Initialize the view
		CausalSphinxApplication.initializeView();

		// Make the data table filter react to the clearing of the filter field
		$('.dataTables_wrapper .dataTables_filter input').clearable({
			onClear: function() {
				CausalSphinxApplication.datatable.fnFilter('');
			}
		});
	});
}(jQuery));