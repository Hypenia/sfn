<?php

if (!elgg_get_plugin_setting('enable_maps', 'sbw_campaigns')) {
	forward('', '404');
}

elgg_push_breadcrumb(elgg_echo('campaigns'), '/campaigns');
elgg_push_breadcrumb(elgg_echo('campaigns:map'));

$content = elgg_view('page/components/map', [
	'src' => elgg_http_add_url_query_elements('campaigns/map', [
		'view' => 'json',
	]),
	'show_search' => true,
	'zoom' => 5,
	'layer_options' => [
		'minZoom' => 5,
	],
]);

$vars['filter_context'] = 'map';

$filter = elgg_view('campaigns/filter', $vars);

$params = array(
    'content' => $content,
    'title' => $title,
    'filter' => $filter,
	'ajax_tabs' => false,
);

$body = elgg_view_layout('content', $params);

echo elgg_view_page($title, $body);

