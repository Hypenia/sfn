<?php

use SBW\Campaigns\Campaign;
use SBW\Campaigns\Commitment;

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof Campaign || !$entity->canEdit()) {
	return;
}

$list = elgg_list_entities_from_relationship([
	'types' => 'object',
	'subtypes' => Commitment::SUBTYPE,
	'container_guids' => $entity->guid,
	'list_type' => 'table',
	'columns' => [
		elgg()->table_columns->transaction_id(),
		elgg()->table_columns->time_created(null, [
			'format' => 'M j, Y H:i',
		]),
		elgg()->table_columns->customer(),
		elgg()->table_columns->merchant(),
		elgg()->table_columns->relief_items(),
		elgg()->table_columns->payment_status(),
	],
	'item_class' => 'payments-transaction',
		]);

if ($list) {
	$link = [
		'name' => 'transactions:download',
		'href' => "campaigns/download/$entity->guid/commitments?view=csv",
		'text' => elgg_echo('download'),
		'link_class' => 'elgg-button elgg-button-action',
	];

	$footer = elgg_view_menu('campaigns:listing:tranactions', [
		'items' => [$link],
		'class' => 'elgg-menu-hz',
	]);
	echo elgg_view_module('aside', '', $list, [
		'footer' => $footer,
	]);
} else {
	echo elgg_format_element('p', [
		'class' => 'elgg-no-results',
	], elgg_echo('campaigns:transactions:no_results'));
}
