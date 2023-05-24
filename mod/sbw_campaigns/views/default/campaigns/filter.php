<?php

$context = 'campaigns';
$filter_context = elgg_extract('filter_context', $vars, 'all');

$tabs = array(
	'active' => array(
		'text' => elgg_echo('campaigns:tabs:active'),
		'href' => "$context/active",
		'selected' => ($filter_context == 'active'),
		'priority' => 100,
	),
	'all' => array(
		'text' => elgg_echo('all'),
		'href' => (isset($vars['all_link'])) ? $vars['all_link'] : "$context/all",
		'selected' => ($filter_context == 'all'),
		'priority' => 200,
	),
);

if (elgg_get_plugin_setting('enable_maps', 'sbw_campaigns')) {
	$tabs['map'] = array(
		'text' => elgg_echo('campaigns:tabs:map'),
		'href' => "$context/map",
		'selected' => ($filter_context == 'map'),
		'priority' => 250,
	);
}

if (elgg_is_logged_in()) {
	$username = elgg_get_logged_in_user_entity()->username;
	$tabs['mine'] = [
		'text' => elgg_echo('mine'),
		'href' => (isset($vars['mine_link'])) ? $vars['mine_link'] : "$context/owner/$username",
		'selected' => ($filter_context == 'mine'),
		'priority' => 300,
	];
	$tabs['friend'] = [
		'text' => elgg_echo('friends'),
		'href' => (isset($vars['friend_link'])) ? $vars['friend_link'] : "$context/friends/$username",
		'selected' => ($filter_context == 'friends'),
		'priority' => 400,
	];
}

foreach ($tabs as $name => $tab) {
	$tab['name'] = $name;
	elgg_register_menu_item('filter', $tab);
}

echo elgg_view('page/layouts/elements/filter', $vars);
