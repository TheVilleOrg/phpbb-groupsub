(function($) {

'use strict';

var termTable = $('#term_table tbody'),
	termHtml = termTable.html();

$(document).on('click', '.move_term_up', function() {
	var row = $(this).parents('tr'),
		prev = row.prev();
	row.insertBefore(prev);

	return false;
});

$(document).on('click', '.move_term_down', function() {
	var row = $(this).parents('tr'),
		next = row.next();
	row.insertAfter(next);

	return false;
});

$(document).on('click', '.delete_term', function() {
	$(this).parents('tr').remove();

	return false;
});

$('#add_edit_pkg').on('reset', function() {
	termTable.html(termHtml);
});

}(jQuery));
