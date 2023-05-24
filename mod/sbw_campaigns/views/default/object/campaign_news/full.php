<?php

use SBW\Campaigns\NewsItem;

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof NewsItem) {
	return;
}

$icon_size = elgg_extract('size', $vars, 'small');
$icon = elgg_view_entity_icon($entity, $icon_size, [
	'href' => false,
	'use_link' => false,
		]);

$content .= elgg_view('campaigns/modules/stats', [
	'entity' => $container,
		]);

$content = '';
if ($entity->hasIcon('master')) {
	$content .= elgg_view_entity_icon($entity, 'master');
}

$content .= elgg_view('output/longtext', [
	'value' => $entity->description,
		]);

$body = elgg_format_element('div', [
	'class' => 'campaigns-full-listing',
		], $content);


$subtitle = [];
$subtitle[] = elgg_view('page/elements/by_line', $vars);

$params = $vars;
$params['sort_by'] = 'priority';
$params['class'] = 'elgg-menu-hz';
$metadata = elgg_view_menu('entity', $params);

$summary = elgg_view('object/elements/summary', [
	'entity' => $entity,
	'title' => false,
	'subtitle' => implode('<br />', $subtitle),
	'metadata' => $metadata,
		]);

echo elgg_view('object/elements/full', [
	'entity' => $entity,
	'summary' => $summary,
	'body' => $body,
]);
