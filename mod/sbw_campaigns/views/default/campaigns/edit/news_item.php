<?php

$news_item = elgg_extract('entity', $vars);
if ($news_item) {
	$title = elgg_echo('campaigns:news:edit');
} else {
	$title = elgg_echo('campaigns:news:add');
}

if (elgg_is_sticky_form('campaigns/edit/news_item')) {
	$sticky = elgg_get_sticky_values('campaigns/edit/news_item');
	elgg_clear_sticky_form('campaigns/edit/news_item');
	$vars = array_merge($vars, $sticky);
}

$body = elgg_view_form('campaigns/edit/news_item', [
	'enctype' => 'multipart/form-data',
], $vars);

echo elgg_view_module('aside', $title, $body, [
	'id' => 'campaigns-news-form',
]);