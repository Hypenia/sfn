<?php

elgg_require_js('campaigns/edit/about');

if (elgg_is_sticky_form('campaigns/edit/about')) {
	$sticky = elgg_get_sticky_values('campaigns/edit/about');
	elgg_clear_sticky_form('campaigns/edit/about');
	$vars = array_merge($vars, $sticky);
}

echo elgg_view_form('campaigns/edit/about', [
	'enctype' => 'multipart/form-data',
], $vars);