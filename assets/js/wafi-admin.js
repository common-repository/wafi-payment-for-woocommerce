jQuery(function ($) {
	'use strict';

	/**
	 * Object to handle Wafi admin functions.
	 */
	var wafi_woocommerce_admin = {
		isTestMode: function () {
			return $('#woocommerce_wafi_testmode').is(':checked');
		},

		/**
		 * Initialize.
		 */
		init: function () {
			$(document.body).on('change', '#woocommerce_wafi_testmode', function () {
				var test_api_key = $('#woocommerce_wafi_test_api_key').parents('tr').eq(0),
					live_api_key = $('#woocommerce_wafi_api_key').parents('tr').eq(0);


				if ($(this).is(':checked')) {

					test_api_key.show();
					live_api_key.hide();
				} else {

					test_api_key.hide();
					live_api_key.show();

				}
			});

			$('#woocommerce_wafi_testmode').trigger('change');
		}
	};

	wafi_woocommerce_admin.init();
});
