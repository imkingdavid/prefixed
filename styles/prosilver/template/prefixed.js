$(document).ready(function() {
	$('#posting_prefixes').show();
	$('#available_prefixes, #used_prefixes').sortable({
		connectWith: '.prefixed_sortable',
		placeholder: 'prefix_placeholder',
		receive: function(event, ui) {
			// Handle filling in the tokens
			if ($(this).parents('#available_prefixes').length) {

			} else if ($(this).parents('#used_prefixes').length) {
				$.get(
					"{U_PREFIXED_PARSE}/" + $(ui).attr('id'), function(data) {
						console.log(data);
					}
				);
			}
		}
	}).disableSelection();
	$('ul.prefixed_sortable li').css('cursor', 'pointer');
});
