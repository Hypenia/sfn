<?php

use SBW\Campaigns\Campaign;

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof Campaign) {
	return;
}

if ($entity->model == Campaign::MODEL_RELIEF) {
	echo elgg_view_field([
		'#type' => 'campaigns/relief_items',
		'#class' => 'campaigns-relief-item-picker',
		'entity' => $entity,
	]);
} else {
	$reward = elgg_extract('reward', $vars, get_input('reward', 'no_reward'));
	echo elgg_view_field([
		'#type' => 'campaigns/rewards',
		'#label' => elgg_echo('campaigns:give:reward'),
		'#class' => 'campaigns-reward-picker',
		'entity' => $entity,
		'required' => true,
		'value' => $reward,
	]);

	$payment_method = elgg_extract('payment_method', $vars);
	echo elgg_view_field([
		'#type' => 'campaigns/payment_method',
		'#label' => elgg_echo('campaigns:give:payment_method'),
		'#class' => 'campaigns-payment-methods',
		'name' => 'payment_method',
		'entity' => $entity,
		'required' => true,
		'value' => $payment_method,
	]);
}

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'guid',
	'value' => $entity->guid,
]);

$submit = elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('next'),
		]);

elgg_set_form_footer($submit);
