define(function (require) {

	var $ = require('jquery');

	$(document).on('change', '.campaigns-give-reward-picker [name="reward"]', function () {
		var $table = $(this).closest('table');
		var $row = $(this).closest('tr');
		$table.find('.campaigns-give-donation-amount').addClass('hidden').find('input').prop('required', false);
		$row.find('.campaigns-give-donation-amount').removeClass('hidden').find('input').prop('required', true);
	});

	$(document).on('click', '.elgg-form-campaigns-give .campaigns-table tr', function (e) {
		$(this).find('input[type="radio"]').prop('checked', true).trigger('change');
	});
});