<?php

namespace hypeJunction\Discovery;

elgg_make_sticky_form('discovery/edit');

$guid = get_input('guid');
$entity = get_entity($guid);

if (!elgg_instanceof($entity) || !$entity->canEdit()) {
	register_error(elgg_echo('actionnotauthorized'));
	forward(REFERRER);
}

$entity->og_title = htmlentities(get_input('og_title', ''), ENT_QUOTES, 'UTF-8');
$entity->og_description = get_input('og_description');
$entity->og_keywords = string_to_tag_array(get_input('og_keywords', ''));
$entity->discoverable = (bool) get_input('discoverable', false);
$entity->embeddable = (bool) get_input('embeddable', false);

if ($entity->save()) {
	$entity->saveIconFromUploadedFile('og_image', 'open_graph_image');
	elgg_clear_sticky_form('discovery/edit');
	system_message(elgg_echo('discovery:site:success'));
} else {
	register_error(elgg_echo('discovery:site:error'));
}