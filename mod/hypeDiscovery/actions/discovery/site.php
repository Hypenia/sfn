<?php

elgg_make_sticky_form('discovery/site');

$site = elgg_get_site_entity();
$site->og_site_name = get_input('og_site_name');
$site->og_description = get_input('og_description');
$site->fb_app_id = get_input('fb_app_id');
$site->twitter_site = get_input('twitter_site');

if ($site->save()) {
	$site->saveIconFromUploadedFile('og_image', 'open_graph_image');

	elgg_clear_sticky_form('discovery/site');

	system_message(elgg_echo('discovery:site:success'));
} else {
	register_error(elgg_echo('discovery:site:error'));
}
