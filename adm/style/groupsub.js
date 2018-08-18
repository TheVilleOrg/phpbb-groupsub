(function($) {

'use strict'

var termTable = $('#term_table tbody'),
	termHtml = termTable.html();

var attachEvents = function() {
	$('.move_term_up').click(function() {
		var row = $(this).parents('tr'),
			prev = row.prev();
		row.insertBefore(prev);

		return false;
	});

	$('.move_term_down').click(function() {
		var row = $(this).parents('tr'),
			next = row.next();
		row.insertAfter(next);

		return false;
	});

	$('.delete_term').click(function() {
		$(this).parents('tr').remove();

		return false;
	});
};

$('#add_edit_pkg').on('reset', function() {
	termTable.html(termHtml);
	attachEvents();
});

attachEvents();

}(jQuery));
