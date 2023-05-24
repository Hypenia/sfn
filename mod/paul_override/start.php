<?php

elgg_register_event_handler('init', 'system', 'paul_override_init');

function paul_override_init() {
	elgg_register_plugin_hook_handler('head', 'page', 'paul_override_head');
	elgg_register_plugin_hook_handler('entity:icon:url', 'user', 'paul_override_icon_url_handler');
	elgg_register_plugin_hook_handler('entity:icon:url', 'group', 'paul_override_groups_icon_url_handler');
}

function paul_override_icon_url_handler($hook, $entity_type, $returnvalue, $params) {

	$user = $params['entity'];
	$size = $params['size'];

	if (isset($user->icontime)) {
		return "avatar/view/$user->username/$size/$user->icontime";
	} else {
		return "mod/paul_override/graphics/user/default{$size}.gif";
	}
}

function paul_override_groups_icon_url_handler($hook, $entity_type, $returnvalue, $params) {

	$group = $params['entity'];
	$size = $params['size'];

	$icontime = $group->icontime;
		
	if ($icontime) {
		// return thumbnail
		return "groupicon/$group->guid/$size/$icontime.jpg";
	}
	return "mod/paul_override/graphics/groups/default{$size}.gif";
}

function paul_override_head($hook, $type, $data) {
	$data['links']['apple-touch-icon'] = array(
		'rel' => 'apple-touch-icon',
		'href' => elgg_get_simplecache_url('paul_override/favicon-128.png'),
	);
	$data['links']['icon-ico'] = array(
		'rel' => 'icon',
		'href' => elgg_get_simplecache_url('paul_override/favicon.ico'),
	);
	$data['links']['icon-16'] = array(
		'rel' => 'icon',
		'sizes' => '16x16',
		'type' => 'image/png',
		'href' => elgg_get_simplecache_url('paul_override/favicon-16.png'),
	);
	$data['links']['icon-32'] = array(
		'rel' => 'icon',
		'sizes' => '32x32',
		'type' => 'image/png',
		'href' => elgg_get_simplecache_url('paul_override/favicon-32.png'),
	);
	$data['links']['icon-64'] = array(
		'rel' => 'icon',
		'sizes' => '64x64',
		'type' => 'image/png',
		'href' => elgg_get_simplecache_url('paul_override/favicon-64.png'),
	);
	$data['links']['icon-128'] = array(
		'rel' => 'icon',
		'sizes' => '128x128',
		'type' => 'image/png',
		'href' => elgg_get_simplecache_url('paul_override/favicon-128.png'),
	);

	return $data;
}