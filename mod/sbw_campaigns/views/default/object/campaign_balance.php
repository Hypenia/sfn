<?php

$entity = elgg_extract('entity', $vars);

$fields = [
	'first_name',
	'last_name',
	'email',
	'phone',
	'company_name',
	'tax_id',
];

foreach ($fields as $field) {
	if (!$entity->$field) {
		continue;
	}
	$label = elgg_echo("campaigns:field:$field");
	$instructions .= elgg_format_element('div', [], "{$label}: {$entity->$field}");
}

$instructions .= elgg_view('output/longtext', [
	'value' => $entity->payout_instructions ? : elgg_echo('campaigns:payout:no_information'),
]);

echo elgg_view_module('aside', elgg_echo('campaigns:payout:details'), $instructions);

echo elgg_view('object/transaction', $vars);