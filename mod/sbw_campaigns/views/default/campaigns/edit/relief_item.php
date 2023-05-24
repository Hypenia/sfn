<?php

$relief_item = elgg_extract('entity', $vars);
if ($relief_item) {
	$title = elgg_echo('campaigns:relief_items:edit');
} else {
	$title = elgg_echo('campaigns:relief_items:add');
}

if (elgg_is_sticky_form('campaigns/edit/relief_item')) {
	$sticky = elgg_get_sticky_values('campaigns/edit/relief_item');
	elgg_clear_sticky_form('campaigns/edit/relief_item');
	$vars = array_merge($vars, $sticky);
}

$body = elgg_view_form('campaigns/edit/relief_item', [
	'enctype' => 'multipart/form-data',
], $vars);

echo elgg_view_module('aside', $title, $body, [
	'id' => 'campaigns-relief-item-form',
]);