<?php

$entity_guid = elgg_extract('guid', $vars);
$entity = get_entity($entity_guid);

if (!$entity instanceof SBW\Campaigns\Campaign) {
	forward('', '404');
}

if (!$entity->canEdit()) {
	register_error(elgg_echo('actionnotauthorized'));
	forward('', '403');
}

elgg_set_config('current_campaign', $entity);

$container = $entity->getContainerEntity();

elgg_set_page_owner_guid($container->guid);

elgg_push_breadcrumb(elgg_echo('campaigns'), '/campaigns');
if ($container instanceof ElggUser) {
	elgg_push_breadcrumb($container->getDisplayName(), "/campaigns/owner/$container->username");
} else {
	elgg_push_breadcrumb($container->getDisplayName(), "/campaigns/group/$container->guid");
}
elgg_push_breadcrumb($entity->getDisplayName(), $entity->getURL());
elgg_push_breadcrumb(elgg_echo('campaigns:edit'));

$vars['entity'] = $entity;

$filter_context = elgg_extract('filter_context', $vars);

if (elgg_view_exists("campaigns/edit/$filter_context")) {
	$content = elgg_view("campaigns/edit/$filter_context", $vars);
} else {
	$vars['filter_context'] = 'about';
	$content = elgg_view('campaigns/edit/about', $vars);
}

if (elgg_language_key_exists("campaigns:edit:{$vars['filter_context']}")) {
	$title = elgg_echo("campaigns:edit:{$vars['filter_context']}", [$entity->getDisplayName()]);
} else {
	$title = elgg_echo('campaigns:edit');
}

if (elgg_is_xhr()) {
	echo elgg_view_module('lightbox', $title, $content);
	return;
}

$sidebar = elgg_view('campaigns/sidebars/owner_block', $vars);
$filter = elgg_view('campaigns/filters/edit', $vars);

$layout = elgg_view_layout('campaign', $vars + [
	'title' => $title,
	'content' => $content,
	'filter' => $filter,
	'sidebar' => $sidebar,
		]);

echo elgg_view_page($title, $layout, 'default', $vars);
