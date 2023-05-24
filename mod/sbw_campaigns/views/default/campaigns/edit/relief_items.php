<?php

use SBW\Campaigns\Campaign;
use SBW\Campaigns\ReliefItem;

$campaign = elgg_extract('entity', $vars);
if (!$campaign instanceof Campaign || $campaign->model !== Campaign::MODEL_RELIEF) {
	return;
}

$options = [
	'no_results' => elgg_echo('campaigns:relief_items:no_results'),
];

echo elgg_list_entities([
	'types' => 'object',
	'subtypes' => ReliefItem::SUBTYPE,
	'container_guids' => (int) $campaign->guid,
	'limit' => 0,
]);

$relief_item_guid = get_input('guid');
$relief_item = get_entity($relief_item_guid);

echo elgg_view('campaigns/edit/relief_item', [
	'container' => $campaign,
	'entity' => $relief_item,
]);

