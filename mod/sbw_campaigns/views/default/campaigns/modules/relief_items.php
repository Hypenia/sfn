<?php

use SBW\Campaigns\Campaign;
use SBW\Campaigns\ReliefItem;

$entity = elgg_extract('entity', $vars);

if (!$entity instanceof Campaign) {
	return;
}

if ($entity->model != Campaign::MODEL_RELIEF) {
	return;
}

echo elgg_list_entities([
	'types' => 'object',
	'subtypes' => ReliefItem::SUBTYPE,
	'container_guids' => (int) $entity->guid,
	'limit' => 0,
	'list_type' => 'gallery',
	'gallery_class' => 'campaigns-rewards campaigns-relief-items',
	'no_results' => elgg_echo('campaigns:relief_items:no_results'),
]);

