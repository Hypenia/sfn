<?php

$entity = elgg_extract('entity', $vars);

echo elgg_list_entities_from_relationship([
	'types' => 'object',
	'subtypes' => SBW\Campaigns\Campaign::SUBTYPE,
	'order_by_metadata' => [
		'name' => 'calendar_end',
		'direction' => 'DESC',
		'as' => 'integer',
	],
	'no_results' => elgg_echo('campaigns:no_results'),
	'preload_owners' => true,
	'preload_containers' => true,
	'relationship' => 'friend',
	'relationship_guid' => (int) $entity->guid,
	'relationship_join_on' => 'owner_guid',
	'list_type' => 'gallery',
	'gallery_class' => 'campaigns-gallery',
]);

