<?php

elgg_register_title_button('campaigns', 'add', 'object', SBW\Campaigns\Campaign::SUBTYPE);

elgg_push_breadcrumb(elgg_echo('campaigns'), '/campaigns');

$vars['filter_context'] = 'active';

$title = elgg_echo('campaigns:active');
$content = elgg_view('campaigns/listing/active', $vars);
$filter = elgg_view('campaigns/filter', $vars);

$layout = elgg_view_layout('campaign', $vars + [
	'title' => $title,
	'content' => $content,
	'filter' => $filter,
]);

echo elgg_view_page($title, $layout, 'default', $vars);