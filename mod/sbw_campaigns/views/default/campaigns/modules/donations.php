<?php

use SBW\Campaigns\Campaign;
use SBW\Campaigns\Donation;

$entity = elgg_extract('entity', $vars);

if (!$entity instanceof Campaign) {
	return;
}

echo elgg_view('campaigns/modules/donation_stats', $vars);

echo elgg_list_entities([
	'list_id' => "donations-{$entity->guid}",
	'types' => 'object',
	'subtypes' => Donation::SUBTYPE,
	'container_guids' => $entity->guid,
	'limit' => 20,
	'offset_key' => 'donations',
	'no_results' => elgg_echo('campaigns:donations:no_results'),
	'pagination' => true,
	'pagination_type' => 'infinite',
]);

