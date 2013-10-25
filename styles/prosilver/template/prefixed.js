$(document).ready(function() {
	$('#available_prefixes, #used_prefixes').sortable({
		connectWith: '.prefix_sortable',
		receive: function(event, ui) {
			// Handle filling in the tokens
			if ($(this).attr('id') == 'available_prefixes') {

			} else if ($(this).attr('id') == 'used_prefixes') {

			}
		}
	}).disableSelection();;
});
