<?php

$entity = elgg_extract('entity', $vars);

echo elgg_view_field([
	'#type' => 'plaintext',
	'#label' => elgg_echo('campaigns:payout:account'),
	'#help' => elgg_echo('campaigns:payout:account:help'),
	'value' => $entity->payout_instructions,
	'name' => 'payout_instructions',
	'required' => true,
]);

echo elgg_view_field([
	'#type' => 'fieldset',
	'legend' => elgg_echo('campaigns:payout:recipient'),
	'align' => 'horizontal',
	'fields' => [
			[
			'#type' => 'email',
			'#label' => elgg_echo('campaigns:checkout:email'),
			'name' => 'email',
			'required' => true,
			'value' => elgg_extract('email', $vars, $entity->email),
		],
			[
			'#type' => 'text',
			'#label' => elgg_echo('campaigns:checkout:first_name'),
			'name' => 'first_name',
			'required' => true,
			'value' => elgg_extract('first_name', $vars, $entity->first_name),
		],
			[
			'#type' => 'text',
			'#label' => elgg_echo('campaigns:checkout:last_name'),
			'name' => 'last_name',
			'required' => true,
			'value' => elgg_extract('last_name', $vars, $entity->last_name),
		],
			[
			'#type' => 'text',
			'#label' => elgg_echo('campaigns:checkout:phone'),
			'name' => 'phone',
			'required' => true,
			'value' => elgg_extract('phone', $vars, $entity->phone),
		],
			[
			'#type' => 'text',
			'#label' => elgg_echo('campaigns:checkout:company_name'),
			'name' => 'company_name',
			'value' => elgg_extract('company_name', $vars, $entity->company_name),
		],
			[
			'#type' => 'text',
			'#label' => elgg_echo('campaigns:checkout:tax_id'),
			'name' => 'tax_id',
			'value' => elgg_extract('tax_id', $vars, $entity->tax_id),
		],
	]
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
