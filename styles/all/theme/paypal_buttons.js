(function($) {

'use strict';

paypal.Buttons({
	style: {
		label: 'pay',
		height: 30,
	},
	createOrder: function() {
		return fetch(paypal_button_config.u_create, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: 'term_id=' + paypal_button_config.term_id,
		}).then(function(res) {
			return res.text();
		});
	},
	onApprove: function(data, actions) {
		return fetch(paypal_button_config.u_capture, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: 'order_id=' + data.orderID,
		}).then(function(res) {
			if (res.status === 200) {
				$('.submit-buttons').remove();
				$('#paypal-success').show();
			} else {
				actions.restart();
			}
		});
	},
}).render('#paypal-button-container');

}(jQuery));
