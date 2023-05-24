<?php

use SBW\Campaigns\Campaign;

$entity = elgg_extract('entity', $vars);

if (!$entity instanceof Campaign) {
	return;
}

$managers = get_members_of_access_collection($entity->write_access_id);
if (!$managers) {
	return;
}

echo elgg_view_entity_list($managers, [
	'full_view' => false,
	'pagination' => false,
	'pagination_type' => false,
]);