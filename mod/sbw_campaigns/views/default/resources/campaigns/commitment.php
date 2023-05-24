<?php

use SBW\Campaigns\Campaign;
use SBW\Campaigns\Commitment;
use SBW\Campaigns\Menus;

elgg_gatekeeper();

$guid = elgg_extract('guid', $vars);
$filter_context = elgg_extract('filter_context', $vars, 'view');

$commitment = get_entity($guid);
if (!$commitment instanceof Commitment) {
	forward('', '404');
}

$campaign = $commitment->getContainerEntity();
if (!$campaign instanceof Campaign || !$campaign->canEdit()) {
	forward('', '403');
}

elgg_push_breadcrumb(elgg_echo('campaigns'), '/campaigns');
$container = $campaign->getContainerEntity();
if ($container instanceof ElggUser) {
	elgg_push_breadcrumb($campaign->getDisplayName(), "/campaigns/owner/$container->username");
} else {
	elgg_push_breadcrumb($campaign->getDisplayName(), "/campaigns/group/$container->guid");
}

elgg_push_breadcrumb($campaign->getDisplayName(), $campaign->getURL());

elgg_push_breadcrumb(elgg_echo('campaigns:commitments'), "/campaigns/edit/$campaign->guid/commitments");

if (!elgg_view_exists("campaigns/commitments/$filter_context")) {
	$filter_context = 'view';
}

switch ($filter_context) {
	case 'view' :
		$items = Menus::getCommitmentMenuItems($commitment);
		foreach ($items as $item) {
			$item->addLinkClass('elgg-button elgg-button-action');
			elgg_register_menu_item('title', $item);
		}
		break;
}

$vars['entity'] = $commitment;
$vars['filter_context'] = $filter_context;

$content = elgg_view("campaigns/commitments/$filter_context", $vars);
if (!$content) {
	forward('', '404');
}

if (elgg_is_xhr()) {
	echo elgg_view_module('lightbox', $title, $content);
	return;
}

$layout = elgg_view_layout('content', [
	'title' => elgg_echo('campaigns:commitment'),
	'content' => $content,
	'entity' => $commitment,
	'filter' => '',
		]);

echo elgg_view_page($title, $layout, 'default', [
	'entity' => $commitment,
]);
