<?php

$entity = elgg_extract('entity', $vars);

echo elgg_view('output/url', [
	'text' => elgg_echo('campaigns:cancel'),
	'href' => "action/campaigns/cancel?guid=$entity->guid",
	'is_action' => true,
	'confirm' => true,
	'class' => 'elgg-button elgg-button-cancel',
]);