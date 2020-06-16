(function($) {

'use strict';

var package_id;
var term_id;

$(document).ready(function() {
	$('#sub_package').on('change', function() {
		populateTerms($(this).val());
	});

	$('#terms').on('change', 'input', function() {
		package_id = $('#sub_package').val();
		term_id = $(this).val();
		updateEnd();
	});

	$('#sub_expire').on('change', function() {
		$('#terms input').prop('checked', false);
	});

	$('#sub_start').on('change', function() {
		updateEnd();
	});

	populateTerms($('#sub_package').val());
});

function pad(n) {
	return (n < 10) ? '0' + n : n;
}

function updateEnd() {
	if (!package_id || !term_id) {
		$('#sub_expire').val('');
		return;
	}

	var start = $('#sub_start').val().split('-');

	var date = new Date(start[0], start[1] - 1, start[2]);
	date.setDate(date.getDate() + terms[package_id][term_id].DAYS);

	var y = date.getFullYear();
	var m = pad(date.getMonth() + 1);
	var d = pad(date.getDate());
	$('#sub_expire').val(y + '-' + m + '-' + d);
}

function populateTerms(id) {
	$('#terms').empty();

	if (terms[id]) {
		term_id = id;
		for (var i = 0; i < terms[id].length; i++) {
			$('<label><input type="radio" name="term" value="' + i + '"> ' + terms[id][i].PRICE + ' / ' + terms[id][i].LENGTH + '</label><br>').appendTo($('#terms'));
		}
	}
}

}(jQuery));
