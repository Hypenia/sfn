<?php

namespace hypeJunction\Discovery;

elgg_unregister_css('font-awesome');
elgg_unregister_css('elgg');
elgg_unregister_css('lightbox');
//elgg_unregister_js('jquery');
elgg_unregister_js('jquery-ui');
elgg_unregister_plugin_hook_handler('output:before', 'page', '_elgg_views_send_header_x_frame_options');

$entity = elgg_extract('entity', $vars);
if (!$entity) {
	return;
}

$title = get_discovery_title($entity);

$content = elgg_view('framework/discovery/public', $vars);

echo elgg_view_page($title, $content);