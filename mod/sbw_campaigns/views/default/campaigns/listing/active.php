<?php

echo elgg_list_entities_from_metadata([
	'types' => 'object',
	'subtypes' => SBW\Campaigns\Campaign::SUBTYPE,
	'metadata_name_value_pairs' => [
		// Show only campaigns that haven't been ended. The 'ended'
		// metadata will be FALSE until the campaign gets ended (either
		// by cron or manual ending). After that the value is timestamp
		// of the ending time.
		[
			'name' => 'ended',
			'value' => false,
		],
	],
	'order_by_metadata' => [
		'name' => 'calendar_start',
		'direction' => 'ASC',
		'as' => 'integer',
	],
	'no_results' => elgg_echo('campaigns:no_results'),
	'preload_owners' => true,
	'preload_containers' => true,
	'list_type' => 'gallery',
	'gallery_class' => 'campaigns-gallery',
]);
