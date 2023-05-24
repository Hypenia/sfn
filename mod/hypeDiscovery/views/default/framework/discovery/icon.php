<?php

$entity = elgg_extract('entity', $vars);
/* @var $entity \ElggEntity */

if (!elgg_instanceof($entity)) {
	return;
}

$class = elgg_extract('img_class', $vars, '');

$title = htmlspecialchars($entity->getDisplayName(), ENT_QUOTES, 'UTF-8', false);

$url = $entity->getURL();
if (isset($vars['href'])) {
	$url = $vars['href'];
}

$size = elgg_extract('size', $vars, 'medium');
$og_sizes = [
	'_og' => 'small',
	'_og_large' => 'medium',
	'_og_high' => 'large'
];

if (isset($og_sizes[$size])) {
	$size = $og_sizes[$size];
}

if (!$entity->hasIcon($size, 'open_graph_image')) {
	return;
}

$img_params = array(
	'src' => $entity->getIconURL([
		'size' => $size,
		'type' => 'open_graph_image',
	]),
	'alt' => $title,
);

if (!empty($class)) {
	$img_params['class'] = $class;
}

if (!empty($vars['width'])) {
	$img_params['width'] = $vars['width'];
}

if (!empty($vars['height'])) {
	$img_params['height'] = $vars['height'];
}

$img = elgg_view('output/img', $img_params);

if ($url) {
	$params = array(
		'href' => $url,
		'text' => $img,
		'is_trusted' => true,
	);
	$class = elgg_extract('link_class', $vars, '');
	if ($class) {
		$params['class'] = $class;
	}

	echo elgg_view('output/url', $params);
} else {
	echo $img;
}
