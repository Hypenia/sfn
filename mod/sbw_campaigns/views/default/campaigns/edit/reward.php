<?php

$reward = elgg_extract('entity', $vars);
if ($reward) {
	$title = elgg_echo('campaigns:rewards:edit');
} else {
	$title = elgg_echo('campaigns:rewards:add');
}

if (elgg_is_sticky_form('campaigns/edit/reward')) {
	$sticky = elgg_get_sticky_values('campaigns/edit/reward');
	elgg_clear_sticky_form('campaigns/edit/reward');
	$vars = array_merge($vars, $sticky);
}

$body = elgg_view_form('campaigns/edit/reward', [
	'enctype' => 'multipart/form-data',
], $vars);

echo elgg_view_module('aside', $title, $body, [
	'id' => 'campaigns-reward-form',
]);