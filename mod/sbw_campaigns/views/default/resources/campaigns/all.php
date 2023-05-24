<?php

elgg_register_title_button('campaigns', 'add', 'object', SBW\Campaigns\Campaign::SUBTYPE);

elgg_push_breadcrumb(elgg_echo('campaigns'), '/campaigns');

$vars['filter_context'] = 'all';

$title = elgg_echo('campaigns:all');
$content = elgg_view('campaigns/listing/all', $vars);
$filter = elgg_view('campaigns/filter', $vars);

$layout = elgg_view_layout('campaign', [
	'title' => $title,
	'content' => $content,
	'filter' => $filter,
	'ajax_tabs' => false,
]);

echo elgg_view_page($title, $layout, 'default', $vars);