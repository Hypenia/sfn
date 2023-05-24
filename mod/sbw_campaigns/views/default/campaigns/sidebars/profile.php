<?php

use SBW\Campaigns\Campaign;

$entity = elgg_extract('entity', $vars);

if (!$entity instanceof Campaign) {
	return;
}

$stats = elgg_view('campaigns/modules/stats', $vars);
if ($stats) {
	$stats = elgg_view_module('aside', false, $stats, [
		'class' => 'campaigns-module',
	]);
}

$share = elgg_view('campaigns/modules/share', $vars);
if ($share) {
	$share = elgg_view_module('aside', false, $share, [
		'class' => 'campaigns-module',
	]);
}

$params = $vars;
$params['full_view'] = true;
$data = elgg_view('campaigns/modules/data', $params);
if ($data) {
	$data = elgg_view_module('aside', false, $data, [
		'class' => 'campaigns-module',
	]);
}

$map = elgg_view('campaigns/modules/map', $params);
if ($map) {
	$map = elgg_view_module('aside', false, $map, [
		'class' => 'campaigns-module',
	]);
}

$managers = elgg_view('campaigns/modules/managers', $params);
if ($managers) {
	$managers = elgg_view_module('aside', elgg_echo('campaigns:field:managers'), $managers, [
		'class' => 'campaigns-module',
	]);
}

$menu = elgg_view('campaigns/modules/menu', $vars);
if ($menu) {
	$menu = elgg_view_module('aside', '', $menu, [
		'class' => 'campaigns-module',
	]);
}

$rewards = elgg_view('campaigns/modules/rewards', $vars);
$relief_items = elgg_view('campaigns/modules/relief_items', $vars);

echo $stats;
echo $share;
echo $menu;
echo $data;
echo $map;
echo $managers;
echo $rewards;
echo $relief_items;