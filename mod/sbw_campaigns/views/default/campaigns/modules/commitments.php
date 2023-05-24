<?php

use SBW\Campaigns\Campaign;
use SBW\Campaigns\Commitment;

$entity = elgg_extract('entity', $vars);

if (!$entity instanceof Campaign) {
	return;
}

if ($entity->model != Campaign::MODEL_RELIEF) {
	return;
}

echo elgg_list_entities_from_metadata([
	'types' => 'object',
	'subtypes' => Commitment::SUBTYPE,
	'container_guids' => $entity->guid,
	'limit' => 20,
	'offset_key' => 'commitments',
	'metadata_names' => 'status',
	'metadata_values' => [Commitment::STATUS_CONFIRMED, Commitment::STATUS_RECEIVED],
	'no_results' => elgg_echo('campaigns:commitments:no_results'),
	'item_view' => 'object/campaign_commitment/module_item',
]);
