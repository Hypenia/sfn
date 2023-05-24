<?php

$entity = elgg_extract('entity', $vars);

echo elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo('campaigns:field:status'),
	'required' => true,
	'name' => 'status',
	'value' => $entity->status,
	'options_values' => [
		\SBW\Campaigns\Commitment::STATUS_COMMITTED => elgg_echo('payments:status:committed'),
		\SBW\Campaigns\Commitment::STATUS_CONFIRMED => elgg_echo('payments:status:confirmed'),
		\SBW\Campaigns\Commitment::STATUS_RECEIVED => elgg_echo('payments:status:received'),
	],
]);

echo elgg_view_field([
	'#type' => 'hidden',
	'name' => 'guid',
	'value' => $entity->guid,
]);

$footer = elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('save'),
]);

elgg_set_form_footer($footer);