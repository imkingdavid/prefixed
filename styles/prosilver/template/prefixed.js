$(document).ready(function() {

	$('#used_prefixes').sortable({
		connectWith: '.prefixed_sortable',
		placeholder: 'prefix_placeholder',
		cancel: 'no-prefix-text',
		over: function(event, ui) {
			$('.no-prefix-text:visible').hide();
		}
	}).disableSelection();

	$('#available_prefixes').sortable({
		connectWith: '.prefixed_sortable',
		placeholder: 'prefix_placeholder',
		over: function(event, ui) {
			if ($('#used_prefixes').children().size() < 2) {
				$('.no-prefix-text:hidden').show();
			}
		}
	}).disableSelection();

	// Add data to the form on submission
	$(this).on('submit', '#postform', function(event) {
		var input = $("<input>").attr("type", "hidden").attr("name", "prefixes_used").val($('#used_prefixes').sortable('serialize'));
		console.log($('#used_prefixes').sortable('serialize'));
		$('#postform').append($(input));
	});

	$('#prefix_dropdown_toggle').click(function(e) {
		e.preventDefault();
		$('#prefix_dropdown').toggle();
		$('#prefix_dropdown_toggle i.fa-arrow-down').toggle();
		$('#prefix_dropdown_toggle i.fa-arrow-up').toggle();
	});
});
