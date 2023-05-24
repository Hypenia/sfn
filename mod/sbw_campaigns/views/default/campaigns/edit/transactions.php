<?php

use SBW\Campaigns\Campaign;

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof Campaign || !$entity->canEdit()) {
	return;
}

$list = elgg_view('payments/listing/transactions/merchant', [
	'entity' => $entity,
		]);

if ($list) {
	$link = [
		'name' => 'transactions:download',
		'href' => "campaigns/download/$entity->guid/transactions?view=csv",
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
