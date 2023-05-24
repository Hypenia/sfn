<?php

namespace hypeJunction\Discovery;

use ElggEntity;

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof ElggEntity) {
	return;
}

$type = $entity->getType();
$subtype = $entity->getSubtype() ? : 'default';

if (elgg_view_exists("framework/discovery/public/$type/$subtype")) {
	echo elgg_view("framework/discovery/public/$type/$subtype", $vars);
	return;
}

$title = get_discovery_title($entity);

$owner_icon = '';
$subtitle = '';

$owner = $entity->getOwnerEntity();
if (is_discoverable($owner) && !$owner instanceof \ElggSite) {
	$owner_name = get_discovery_title($owner);
	$owner_url = get_entity_permalink($owner);
	$owner_link = elgg_view('output/url', [
		'href' => $owner_url,
		'text' => $owner_name,
	]);

	$owner_icon = elgg_view('output/img', [
		'src' => $owner->getIconURL([
			'size' => 'small',
			'type' => 'open_graph_image',
		]),
		'alt' => $owner_name,
	]);

	$subtitle = elgg_echo('byline', array($owner_link));
}

$summary = elgg_view('object/elements/summary', [
	'entity' => $entity,
	'title' => false,
	'subtitle' => $subtitle,
	'tags' => elgg_view('output/tags', [
		'value' => get_discovery_keywords($entity),
	]) ?: false,
]);


$image = elgg_view('framework/discovery/icon', array(
	'entity' => $entity,
	'size' => 'medium',
	'img_class' => 'elgg-photo',
));

$content = elgg_view('output/longtext', [
	'value' => get_discovery_description($entity),
]);

$login_url = elgg_view('output/url', [
	'href' => 'login',
	'text' => elgg_echo('login'),
]);

$register_url = elgg_view('output/url', [
	'href' => 'register',
	'text' => elgg_echo('register'),
]);

$more = elgg_format_element('p', [], elgg_echo('discovery:login_for_more', [$login_url, $register_url]));

$content .= elgg_format_element('div', [
	'class' => 'elgg-output',
], $image . $more);

$content = elgg_format_element('div', [
	'class' => 'discovery-content',
], $content);

echo elgg_view('object/elements/full', [
	'entity' => $entity,
	'icon' => $owner_icon,
	'summary' => $summary,
	'body' => $content,
]);
