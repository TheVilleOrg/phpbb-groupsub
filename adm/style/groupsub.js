(function($) {

'use strict'

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

}(jQuery));
