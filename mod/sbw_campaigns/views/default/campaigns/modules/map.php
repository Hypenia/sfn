<?php

if (!elgg_get_plugin_setting('enable_maps', 'sbw_campaigns')) {
	return;
}

if (!elgg_is_active_plugin('hypeMapsOpen')) {
	return;
}

$entity = elgg_extract('entity', $vars);

$svc = new \hypeJunction\MapsOpen\MapsService();
$marker = $svc->getMarker($entity);
if (!$marker) {
	return;
}

echo elgg_view('page/components/map', [
	'markers' => [$marker],
	'center' => $marker,
	'zoom' => 2,
]);