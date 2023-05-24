<?php

use SBW\Campaigns\Campaign;

if (!elgg_get_plugin_setting('upgrade:geocode', 'sbw_campaigns')) {
	// Populate campaign coordinates
	$batch = new ElggBatch('elgg_get_entities_from_metadata', array(
		'types' => 'object',
		'subtypes' => Campaign::SUBTYPE,
		'metadata_name_value_pairs' => array(
				[
				'name' => 'location',
				'value' => '',
				'operand' => '!=',
			],
		),
		'order_by' => 'e.guid ASC',
		'limit' => 0
	));

	foreach ($batch as $b) {
		// Will triger geocoding
		$b->save();
	}

	elgg_set_plugin_setting('upgrade:geocode', time(), 'sbw_campaigns');
}