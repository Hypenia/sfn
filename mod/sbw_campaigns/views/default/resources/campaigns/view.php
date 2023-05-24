<?php

use SBW\Campaigns\Campaign;
use SBW\Campaigns\Menus;

$entity_guid = elgg_extract('guid', $vars);
$entity = get_entity($entity_guid);

if (!$entity instanceof Campaign) {
	forward('', '404');
}

elgg_set_config('current_campaign', $entity);

$container = $entity->getContainerEntity();

elgg_group_gatekeeper(true, $container->guid);

elgg_set_page_owner_guid($container->guid);

elgg_push_breadcrumb(elgg_echo('campaigns'), '/campaigns');
if ($container instanceof ElggUser) {
	elgg_push_breadcrumb($container->getDisplayName(), "/campaigns/owner/$container->username");
} else {
	elgg_push_breadcrumb($container->getDisplayName(), "/campaigns/group/$container->guid");
}

elgg_push_breadcrumb($entity->getDisplayName(), $entity->getURL());

$filter_context = elgg_extract('filter_context', $vars);

$vars['entity'] = $entity;

if (elgg_view_exists("campaigns/view/$filter_context")) {
	$content = elgg_view("campaigns/view/$filter_context", $vars);
} else {
	$vars['filter_context'] = 'about';
	$content = elgg_view('campaigns/view/about', $vars);
}

if (elgg_language_key_exists("campaigns:view:{$vars['filter_context']}")) {
	$title = elgg_echo("campaigns:view:{$vars['filter_context']}", [$entity->getDisplayName()]);
} else {
	$title = $entity->getDisplayName();
}

$items = Menus::getProfileMenuItems($entity);
foreach ($items as &$item) {
	$item->addLinkClass('elgg-button elgg-button-action');
	elgg_register_menu_item('title', $item);
}

if (elgg_is_xhr()) {
	echo elgg_view_module('lightbox', $title, $content);
	return;
}

$filter = elgg_view('campaigns/filters/view', $vars);
$sidebar = elgg_view('campaigns/sidebars/profile', $vars);

$layout = elgg_view_layout('campaign', $vars + [
	'title' => $title,
	'content' => $content,
	'sidebar' => $sidebar,
	'filter' => $filter,
	'class' => 'campaigns-profile-layout-modular',
		]);

echo elgg_view_page($title, $layout, 'default', $vars);
