<?php

/**
 * hypeDiscovery
 * Enhanced presence of Elgg sites in the social eco-system
 * 
 * @author Ismayil Khayredinov <ismayil@hypejunction.com>
 */

namespace hypeJunction\Discovery;

require_once __DIR__ . '/autoloader.php';

elgg_register_event_handler('init', 'system', function() {

	elgg_extend_view('elgg.css', 'discovery.css');
	elgg_extend_view('elements/icons.css', 'webicons.css');
	elgg_register_css('oembed.css', elgg_get_simplecache_url('oembed.css'));
	
	elgg_register_page_handler('permalink', [Router::class, 'permalinkHandler']);
	elgg_register_page_handler('opengraph', [Router::class, 'opengraphHandler']);
	elgg_register_plugin_hook_handler('public_pages', 'walled_garden', [Router::class, 'publicPages']);
	elgg_register_plugin_hook_handler('forward', 'login', [Router::class, 'redirectErrorToPermalink']);
	elgg_register_plugin_hook_handler('forward', '403', [Router::class, 'redirectErrorToPermalink']);
	elgg_register_plugin_hook_handler('forward', '404', [Router::class, 'redirectErrorToPermalink']);

	elgg_register_action('hypeDiscovery/settings/save', __DIR__ . '/actions/settings/save.php');
	elgg_register_action('discovery/site', __DIR__ . '/actions/discovery/site.php', 'admin');
	elgg_register_action('discovery/share', __DIR__ . '/actions/discovery/share.php', 'public');
	elgg_register_action('discovery/edit', __DIR__ . '/actions/discovery/edit.php');

	elgg_register_plugin_hook_handler('register', 'menu:entity', [Menus::class, 'entityMenuSetup']);
	elgg_register_plugin_hook_handler('register', 'menu:extras', [Menus::class, 'extrasMenuSetup']);
	elgg_register_plugin_hook_handler('register', 'menu:discovery_share', [Menus::class, 'shareMenuSetup']);
	elgg_register_plugin_hook_handler('register', 'menu:scraper:card', [Menus::class, 'setupCardMenu']);

	elgg_register_plugin_hook_handler('entity:icon:url', 'all', [Icons::class, 'entityIconURL']); // BC
	elgg_register_plugin_hook_handler('entity:open_graph_image:file', 'all', [Icons::class, 'entityOpenGraphImageFile']);
	elgg_register_plugin_hook_handler('entity:open_graph_image:url', 'all', [Icons::class, 'entityOpenGraphImageURL']);
	elgg_register_plugin_hook_handler('entity:open_graph_image:sizes', 'all', [Icons::class, 'entityOpenGraphImageSizes']);

	// Analytics
	elgg_register_event_handler('login:after', 'user', [Analytics::class, 'saveTempUserHash']);

	// Metatags
	elgg_register_plugin_hook_handler('head', 'page', [Discovery::class, 'prepareMetas']);
	elgg_register_plugin_hook_handler('metatags', 'discovery', [Discovery::class, 'graphExport']);

	// oEmbed
	elgg_register_plugin_hook_handler('head', 'page', [Discovery::class, 'prepareAlternateLinks']);
	elgg_register_plugin_hook_handler('export:entity', 'oembed', [Discovery::class, 'oEmbedExport']);
	elgg_register_plugin_hook_handler('route', 'services', [Router::class, 'servicesRoute']);
	elgg_register_viewtype('oembed');

	elgg_register_menu_item('page', array(
		'name' => 'discovery:settings',
		'href' => 'admin/plugin_settings/hypeDiscovery',
		'text' => elgg_echo('admin:discovery:settings'),
		'context' => 'admin',
		'section' => 'discovery'
	));

	elgg_register_menu_item('page', array(
		'name' => 'discovery:site',
		'href' => 'admin/discovery/site',
		'text' => elgg_echo('admin:discovery:site'),
		'context' => 'admin',
		'section' => 'discovery'
	));
});
