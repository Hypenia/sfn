define(function (require) {

	var elgg = require('elgg');
	var $ = require('jquery');
	var Ajax = require('elgg/Ajax');
	$(document).on('change', '.campaigns-checkbox-register [type="checkbox"]', function (e) {
		if ($(this).prop('checked')) {
			$('.campaigns-checkout-register').slideDown();
			$('[name="username"]').prop('required', true);
			$('[name="password"]').prop('required', true);
		} else {
			$('.campaigns-checkout-register').slideUp();
			$('[name="username"]').prop('required', false);
			$('[name="password"]').prop('required', false);
		}
	});

	$(document).on('change', '.campaigns-billing-as-shipping [type="checkbox"]', function (e) {
		var $field = $(this).closest('.elgg-field');
		if ($(this).prop('checked')) {
			$field.siblings('.elgg-field').hide().find('[required]').prop('required', false);
			$field.siblings('.elgg-field').find('input,select,textarea').each(function() {
				var part = $(this).data('part');
				var value = $('[data-address="shipping"] [data-part="' + part + '"]').val();
				$(this).val(value);
			});
		} else {
			$field.siblings('.elgg-field').show().filter('.elgg-field-required').find('input[type="text"],select').prop('required', true);
		}
	});

	$(document).on('blur', '[name="email"]', function (e) {
		if (elgg.is_logged_in()) {
			return;
		}

		var ajax = new Ajax(false);
		var email = $(this).val();
		ajax.action('campaigns/is_registered', {
			data: {
				email: email
			}
		}).done(function (output) {
			if (output.is_registered) {
				$('.campaigns-checkbox-register').hide()
						.find('input[type="checkbox"]').prop('checked', false).trigger('change');
			} else {
				$('.campaigns-checkbox-register').show();
			}
		});
	});

	$(document).on('change', '[data-address] [data-part]', function() {
		var $form = $(this).closest('form');
		var sync = $('.campaigns-billing-as-shipping [type="checkbox"]', $form).prop('checked');
		if (!sync) {
			return;
		}
		
		var part = $(this).data('part');
		$('[data-address] [data-part="' + part + '"]').not($(this)).val($(this).val());
	});
});


