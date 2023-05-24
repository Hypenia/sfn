<?php

$username = elgg_extract('username', $vars);
$entity_guid = elgg_extract('guid', $vars);
if ($entity_guid) {
	$entity = get_entity($entity_guid);
} else {
	$entity = get_user_by_username($username);
}

if (!$entity instanceof ElggEntity || !$entity->canEdit()) {
	forward('', '404');
}

elgg_group_gatekeeper(true, $entity->guid);

elgg_set_page_owner_guid($entity->guid);

elgg_register_title_button('campaigns', 'add', 'object', SBW\Campaigns\Campaign::SUBTYPE);

elgg_push_breadcrumb(elgg_echo('campaigns'), '/campaigns');
elgg_push_breadcrumb($entity->getDisplayName(), "/campaigns/owner/$entity->username");

$title = elgg_echo('friends');
elgg_push_breadcrumb($title);

if ($entity->guid == elgg_get_logged_in_user_guid()) {
	$vars['filter_context'] = 'friends';
}

$vars['entity'] = $entity;

$content = elgg_view('campaigns/listing/friends', $vars);
$filter = elgg_view('campaigns/filter', $vars);

$layout = elgg_view_layout('campaign', [
	'title' => $title,
	'content' => $content,
	'filter' => $filter,
	'ajax_tabs' => false,
		]);

echo elgg_view_page($title, $layout, 'default', $vars);
