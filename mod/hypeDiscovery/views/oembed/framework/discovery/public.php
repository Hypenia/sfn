<?php

namespace hypeJunction\Discovery;

elgg_load_css('oembed.css');

$entity = elgg_extract('entity', $vars);

if (!elgg_instanceof($entity)) {
	return;
}

$type = $entity->getType();
$subtype = $entity->getSubtype() ? : 'default';

$views = [
	"$type/$subtype",
	"$type/default",
];

foreach ($views as $view) {
	if (elgg_view_exists($view, 'oembed')) {
		echo elgg_view($view, array(
			'entity' => $entity,
			'full_view' => true,
		), false, false, 'oembed');
	}
}

