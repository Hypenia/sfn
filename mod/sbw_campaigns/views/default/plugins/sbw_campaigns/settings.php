<?php

$entity = elgg_extract('entity', $vars);

echo elgg_view_field([
	'#type' => 'longtext',
	'#label' => elgg_echo('campaigns:terms:campaigner'),
	'name' => 'params[terms:campaigner]',
	'value' => $entity->{'terms:campaigner'},
]);

echo elgg_view_field([
	'#type' => 'longtext',
	'#label' => elgg_echo('campaigns:terms:donor'),
	'name' => 'params[terms:donor]',
	'value' => $entity->{'terms:donor'},
]);

echo elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo('campaigns:require_verification'),
	'#help' => elgg_echo('campaigns:require_verification:help'),
	'name' => 'params[require_verification]',
	'value' => $entity->require_verification,
	'options_values' => [
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	],
]);

$cutoff_time_opts = [];
foreach (range(0, 23) as $hour) {
	$cutoff_time_opts[$hour * 3600] = date('H:i', $hour * 3600);
}

echo elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo('campaigns:cutoff_time'),
	'#help' => elgg_echo('campaigns:cutoff_time:help'),
	'name' => 'params[cutoff_time]',
	'value' => isset($entity->cutoff_time) ? $entity->cutoff_time : 12 * 3600,
	'options_values' => $cutoff_time_opts,
]);

echo elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('campaigns:payments:all_or_nothing_fee'),
	'#help' => elgg_echo('campaigns:payments:all_or_nothing_fee:help'),
	'name' => 'params[all_or_nothing_fee]',
	'value' => (float) $entity->all_or_nothing_fee,
]);

echo elgg_view_field([
	'#type' => 'text',
	'#label' => elgg_echo('campaigns:payments:money_pot_fee'),
	'#help' => elgg_echo('campaigns:payments:money_pot_fee:help'),
	'name' => 'params[money_pot_fee]',
	'value' => (float) $entity->money_pot_fee,
]);

if (elgg_is_active_plugin('payments_paypal_api')) {

	echo elgg_view_field([
		'#type' => 'select',
		'#label' => elgg_echo('campaigns:payments:enable_paypal'),
		'#help' => elgg_echo('campaigns:payments:enable_paypal:help'),
		'name' => 'params[enable_paypal]',
		'value' => $entity->enable_paypal,
		'options_values' => [
			0 => elgg_echo('option:no'),
			1 => elgg_echo('option:yes'),
		],
	]);

	echo elgg_view_field([
		'#type' => 'text',
		'#label' => elgg_echo('campaigns:payments:paypal_percentile_fee'),
		'#help' => elgg_echo('campaigns:payments:paypal_percentile_fee:help'),
		'name' => 'params[paypal_percentile_fee]',
		'value' => (float) $entity->paypal_percentile_fee,
	]);

	echo elgg_view_field([
		'#type' => 'text',
		'#label' => elgg_echo('campaigns:payments:paypal_flat_fee'),
		'#help' => elgg_echo('campaigns:payments:paypal_flat_fee:help'),
		'name' => 'params[paypal_flat_fee]',
		'value' => (float) $entity->paypal_flat_fee,
	]);
}

if (elgg_is_active_plugin('payments_wire')) {

	echo elgg_view_field([
		'#type' => 'select',
		'#label' => elgg_echo('campaigns:payments:enable_wire'),
		'#help' => elgg_echo('campaigns:payments:enable_wire:help'),
		'name' => 'params[enable_wire]',
		'value' => $entity->enable_wire,
		'options_values' => [
			0 => elgg_echo('option:no'),
			1 => elgg_echo('option:yes'),
		],
	]);

	echo elgg_view_field([
		'#type' => 'text',
		'#label' => elgg_echo('campaigns:payments:wire_percentile_fee'),
		'#help' => elgg_echo('campaigns:payments:wire_percentile_fee:help'),
		'name' => 'params[wire_percentile_fee]',
		'value' => (float) $entity->wire_percentile_fee,
	]);

	echo elgg_view_field([
		'#type' => 'text',
		'#label' => elgg_echo('campaigns:payments:wire_flat_fee'),
		'#help' => elgg_echo('campaigns:payments:wire_flat_fee:help'),
		'name' => 'params[wire_flat_fee]',
		'value' => (float) $entity->wire_flat_fee,
	]);
}

if (elgg_is_active_plugin('payments_stripe')) {

	echo elgg_view_field([
		'#type' => 'select',
		'#label' => elgg_echo('campaigns:payments:enable_stripe'),
		'#help' => elgg_echo('campaigns:payments:enable_stripe:help'),
		'name' => 'params[enable_stripe]',
		'value' => $entity->enable_stripe,
		'options_values' => [
			0 => elgg_echo('option:no'),
			1 => elgg_echo('option:yes'),
		],
	]);

	echo elgg_view_field([
		'#type' => 'text',
		'#label' => elgg_echo('campaigns:payments:stripe_percentile_fee'),
		'#help' => elgg_echo('campaigns:payments:stripe_percentile_fee:help'),
		'name' => 'params[stripe_percentile_fee]',
		'value' => (float) $entity->stripe_percentile_fee,
	]);

	echo elgg_view_field([
		'#type' => 'text',
		'#label' => elgg_echo('campaigns:payments:stripe_flat_fee'),
		'#help' => elgg_echo('campaigns:payments:stripe_flat_fee:help'),
		'name' => 'params[stripe_flat_fee]',
		'value' => (float) $entity->stripe_flat_fee,
	]);
}

if (elgg_is_active_plugin('payments_sofort')) {

	echo elgg_view_field([
		'#type' => 'select',
		'#label' => elgg_echo('campaigns:payments:enable_sofort'),
		'#help' => elgg_echo('campaigns:payments:enable_sofort:help'),
		'name' => 'params[enable_sofort]',
		'value' => $entity->enable_sofort,
		'options_values' => [
			0 => elgg_echo('option:no'),
			1 => elgg_echo('option:yes'),
		],
	]);

	echo elgg_view_field([
		'#type' => 'text',
		'#label' => elgg_echo('campaigns:payments:sofort_percentile_fee'),
		'#help' => elgg_echo('campaigns:payments:sofort_percentile_fee:help'),
		'name' => 'params[sofort_percentile_fee]',
		'value' => (float) $entity->sofort_percentile_fee,
	]);

	echo elgg_view_field([
		'#type' => 'text',
		'#label' => elgg_echo('campaigns:payments:sofort_flat_fee'),
		'#help' => elgg_echo('campaigns:payments:sofort_flat_fee:help'),
		'name' => 'params[sofort_flat_fee]',
		'value' => (float) $entity->sofort_flat_fee,
	]);
}

echo elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo('campaigns:enable_maps'),
	'#help' => elgg_echo('campaigns:enable_maps:help'),
	'name' => 'params[enable_maps]',
	'value' => $entity->enable_maps,
	'options_values' => [
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	],
]);
