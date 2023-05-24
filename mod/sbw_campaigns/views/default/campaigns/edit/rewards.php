<?php

use SBW\Campaigns\Campaign;

$campaign = elgg_extract('entity', $vars);
if (!$campaign instanceof Campaign) {
	return;
}

$options = [
	'no_results' => elgg_echo('campaigns:rewards:no_results'),
];

echo elgg_list_entities_from_metadata($campaign->getRewards($options, false));

$reward_guid = get_input('guid');
$reward = get_entity($reward_guid);

if (!$campaign->started || elgg_is_admin_logged_in()) {
	echo elgg_view('campaigns/edit/reward', [
		'container' => $campaign,
		'entity' => $reward,
	]);
}