CausalSphinxApplication = {
	datatable: null,
	// Initializes the data table, depending on the current view
	initializeView: function() {
		this.datatable = $('#tx-sphinx-kickstart-list').dataTable({
			'bPaginate': false,
			'bJQueryUI': true,
			'bLengthChange': false,
			'iDisplayLength': 15,
			'bStateSave': true
		});

		// restore filter
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