<?php

use SBW\Campaigns\Campaign;

$campaign = elgg_extract('entity', $vars);
if (!$campaign instanceof Campaign) {
	return;
}

$options = [
	'no_results' => elgg_echo('campaigns:news:no_results'),
];

echo elgg_list_entities([
	'types' => 'object',
	'subtypes' => SBW\Campaigns\NewsItem::SUBTYPE,
	'container_guids' => $campaign->guid,
	'limit' => 0,
	'full_view' => false,
]);

$news_guid = get_input('guid');
$news_item = get_entity($news_guid);

echo elgg_view('campaigns/edit/news_item', [
	'container' => $campaign,
	'entity' => $news_item,
]);