define(function (require) {
	var elgg = require('elgg');
	var $ = require('jquery');
	var moment = require('moment');
	require('jquery-ui');

	$('.campaigns-field-calendar-start,.capaigns-field-calendar-end').datepicker('option', 'onSelect', function (dateText, instance) {
		if ($(this).is('.elgg-input-timestamp')) {
			var timestamp = Date.UTC(instance.selectedYear, instance.selectedMonth, instance.selectedDay);
			timestamp = timestamp / 1000;
			$('input[rel="' + this.id + '"]').val(timestamp);
		}
		if (dateText !== instance.lastVal) {
			// trigger change event
			$(this).change();
		}

		var $form = $(this).closest('form');
		var $startDate = $('.campaigns-field-calendar-start', $form);
		var $endDate = $('.campaigns-field-calendar-end', $form);
		var startDate = $startDate.val();
		var endDate = $endDate.val();
		
		if ($(this).is('.campaigns-field-calendar-start')) {
			if (moment(startDate).isAfter(endDate)) {
				$endDate.val(startDate);
			}
			$endDate.datepicker('option', 'minDate', startDate);
		} else if ($(this).is('.campaigns-field-calendar-end')) {
			if (moment(endDate).isBefore(startDate)) {
				$startDate.val(endDate);
			}
			$startDate.datepicker('option', 'maxDate', startDate);
		}

	});

	$(document).on('change', 'input[name="model"]', function() {
		var $form = $(this).closest('form');
		if ($(this).val() === 'relief') {
			$('.campaigns-field-monetary', $form).addClass('hidden').find('.elgg-field-required').find('input,textarea').prop('required', false);
			$('.campaigns-field-relief', $form).removeClass('hidden').find('.elgg-field-requied').find('input,textarea').prop('required', true);
		} else {
			$('.campaigns-field-monetary', $form).removeClass('hidden').find('.elgg-field-required').find('input,textarea').prop('required', true);
			$('.campaigns-field-relief', $form).addClass('hidden').find('.elgg-field-required').find('input,textarea').prop('required', false);
		}
	});

	// make required hidden fields not required
	$('input,textarea').not(':visible').prop('required', false);
});