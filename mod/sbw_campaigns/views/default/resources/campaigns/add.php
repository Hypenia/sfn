<?php

$container_guid = elgg_extract('container_guid', $vars);
$container = get_entity($container_guid);

if (!$container || !$container->canWriteToContainer(0, 'object', SBW\Campaigns\Campaign::SUBTYPE)) {
	register_error(elgg_echo('actionnotauthorized'));
	forward('', '403');
}

elgg_group_gatekeeper(true, $container->guid);

elgg_set_page_owner_guid($container->guid);

elgg_set_config('current_campaign', false); // prevent campaign ACLs from showing up in the picker

elgg_push_breadcrumb(elgg_echo('campaigns'), '/campaigns');
if ($container instanceof ElggUser) {
	elgg_push_breadcrumb($container->getDisplayName(), "/campaigns/owner/$container->username");
} else {
	elgg_push_breadcrumb($container->getDisplayName(), "/campaigns/group/$container->guid");
}

elgg_push_breadcrumb(elgg_echo('campaigns:add'));

$vars['container'] = $container;

$title = elgg_echo('campaigns:add');
$content = elgg_view('campaigns/edit/about', $vars);
$filter = elgg_view('campaigns/filters/edit', $vars);

$layout = elgg_view_layout('campaign', $vars + [
	'title' => $title,
	'content' => $content,
	'filter' => $filter,
]);

echo elgg_view_page($title, $layout, 'default', $vars);