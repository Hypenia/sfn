<?php

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \SBW\Campaigns\Campaign) {
	return;
}

$icon_size = elgg_extract('size', $vars, 'large');
$icon = elgg_view_entity_icon($entity, $icon_size);

$content = elgg_view('output/longtext', [
	'value' => $entity->briefdescription,
]);

$content .= elgg_view('campaigns/modules/stats', [
	'entity' => $entity,
]);

$subtitle = [];

$container = $entity->getContainerEntity();
if ($container) {
	$link = elgg_view('output/url', [
		'href' => $container->getURL(),
		'text' => $container->getDisplayName(),
	]);
	$subtitle[] = elgg_echo('byline', [$link]);
}

$subtitle[] = elgg_echo("campaigns:model:$entity->model");

if (!$entity->isPublished()) {
	$subtitle[] = elgg_echo('campaigns:status:draft');
} else if (!$entity->isVerified()) {
	$subtitle[] = elgg_echo('campaigns:status:pending_verification');
}

if ($entity->website) {
	$subtitle[] = elgg_view_icon('globe') . elgg_view('output/url', [
		'href' => $entity->website,
		'target' => '_blank',
	]);
}

$metadata = '';
if (!elgg_in_context('widgets')) {
	$params = $vars;
	$params['sort_by'] = 'priority';
	$params['class'] = 'elgg-menu-hz';
	$metadata = elgg_view_menu('entity', $params);
}

$summary = elgg_view('object/elements/summary', [
	'entity' => $entity,
	'subtitle' => implode('<br />', $subtitle),
	'content' => $content,
	'metadata' => $metadata,
		]);

echo elgg_view_image_block($icon, $summary);
