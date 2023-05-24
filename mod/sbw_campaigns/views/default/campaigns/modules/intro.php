<?php

use SBW\Campaigns\Campaign;

$entity = elgg_extract('entity', $vars);

if (!$entity instanceof Campaign) {
	return;
}

$icon = elgg_view_entity_icon($entity, 'medium');

echo elgg_view('object/elements/summary', [
	'entity' => $entity,
	'tags' => false,
	'subtitle' => $entity->briefdescription,
	'icon' => $icon,
]);