$(document).ready(function() {
	$('#posting_prefixes').show();
	$('#available_prefixes, #used_prefixes').sortable({
		connectWith: '.prefixed_sortable',
		placeholder: 'prefix_placeholder',
		receive: function(event, ui) {
			// Handle filling in the tokens
			var ul = $(this).closest('ul.prefixed_sortable');
			if (ul.attr('id') == 'used_prefixes') {
			} else if (ul.attr('id') == 'available_prefixes') {
				// $.get(
				// 	"{U_PREFIXED_PARSE}/" + $(ui).attr('id'), function(data) {
				// 		console.log(data);
				// 	}
				// );
			}
		}
	}).disableSelection();
	$('ul.prefixed_sortable li').css('cursor', 'pointer');

	// Add data to the form on submission
	$(this).on('submit', '#postform', function(event) {
		var input = $("<input>").attr("type", "hidden").attr("name", "prefixes_used").val($('#used_prefixes').sortable('serialize'));
		console.log($('#used_prefixes').sortable('serialize'));
		$('#postform').append($(input));
	});
});
