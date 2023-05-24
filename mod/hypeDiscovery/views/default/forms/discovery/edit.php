<?php

namespace hypeJunction\Discovery;

$entity = elgg_extract('entity', $vars);

echo elgg_format_element('div', [
	'class' => 'elgg-text-help',
], elgg_echo('discovery:og:help'));

if (is_discoverable_type($entity)) {
	echo elgg_view_input('select', array(
		'name' => 'discoverable',
		'value' => (isset($entity->discoverable)) ? (bool) $entity->discoverable : is_discoverable($entity),
		'options_values' => array(
			0 => elgg_echo('option:no'),
			1 => elgg_echo('option:yes'),
		),
		'label' => elgg_echo('discovery:og:discoverable'),
		'help' => elgg_echo('discovery:og:discoverable:help'),
	));
}

if (is_embeddable_type($entity)) {
	echo elgg_view_input('select', array(
		'name' => 'embeddable',
		'value' => (isset($entity->discoverable)) ? (bool) $entity->embeddable : is_embeddable($entity),
		'options_values' => array(
			0 => elgg_echo('option:no'),
			1 => elgg_echo('option:yes'),
		),
		'label' => elgg_echo('discovery:og:embeddable'),
		'help' => elgg_echo('discovery:og:embeddable:help'),
	));
}

echo elgg_view_input('file', array(
	'name' => 'og_image',
	'value' => $entity->hasIcon('large', 'open_graph_image'),
	'label' => elgg_echo('discovery:og:image'),
	'help' => elgg_echo('discovery:og:site_image:help'),
));

foreach (['open_graph_image', 'cover', 'icon'] as $type) {
	if ($entity->hasIcon('large', $type)) {
		echo elgg_view('output/img', [
			'src' => $entity->getIconURL('large', $type),
			'class' => 'elgg-photo elgg-field',
		]);
	}
}

echo elgg_view_input('text', array(
	'name' => 'og_title',
	'value' => $entity->og_title,
	'label' => elgg_echo('discovery:og:title'),
	'help' => elgg_echo('discovery:og:title:help'),
));

echo elgg_view_input('text', array(
	'name' => 'og_description',
	'value' => $entity->og_description,
	'label' => elgg_echo('discovery:og:description'),
	'help' => elgg_echo('discovery:og:description:help'),
));

echo elgg_view_input('tags', array(
	'name' => 'og_keywords',
	'value' => $entity->og_keywords,
	'label' => elgg_echo('discovery:og:keywords'),
	'help' => elgg_echo('discovery:og:keywords:help'),
));

echo elgg_view_input('hidden', array(
	'name' => 'guid',
	'value' => $entity->guid
));

echo elgg_view_input('submit', array(
	'value' => elgg_echo('save'),
	'field_class' => 'elgg-foot',
));
?>
<script>
	require(['forms/discovery/edit']);
</script>