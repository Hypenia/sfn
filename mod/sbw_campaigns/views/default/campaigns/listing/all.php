<?php

echo elgg_list_entities_from_metadata([
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
	'list_type' => 'gallery',
	'gallery_class' => 'campaigns-gallery',
]);
