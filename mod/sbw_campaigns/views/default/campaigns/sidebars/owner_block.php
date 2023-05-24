<?php

use SBW\Campaigns\Campaign;

$entity = elgg_extract('entity', $vars);

if (!$entity instanceof Campaign) {
	return;
}

$intro = elgg_view('campaigns/modules/intro', $vars);
if ($intro) {
	$intro = elgg_view_module('aside', false, $intro, [
		'class' => 'campaigns-module',
	]);
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

$menu = elgg_view('campaigns/modules/menu', $vars);
if ($menu) {
	$menu = elgg_view_module('aside', '', $menu, [
		'class' => 'campaigns-module',
	]);
}

echo $intro;
echo $stats;
echo $menu;
echo $data;
echo $share;
