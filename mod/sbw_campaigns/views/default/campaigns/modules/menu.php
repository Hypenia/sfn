<?php

use SBW\Campaigns\Campaign;
use SBW\Campaigns\Menus;

$entity = elgg_extract('entity', $vars);

if (!$entity instanceof Campaign) {
	return;
}


$skip = ['access', 'give', 'follow', 'unfollow', 'likes', 'unlike', 'likes_count', 'discovery:share'];

$sections = elgg()->menus->getMenu('entity', [
			'entity' => $entity,
		])->getSections();
foreach ($sections as $section => $items) {
	foreach ($items as $item) {
		if (in_array($item->getName(), $skip)) {
			continue;
		}
		if (in_array($item->getName(), ['discovery:edit'])) {
			$text = $item->getText();
			$text .= $item->getTooltip();
			$item->setText($text);
		}
		elgg_register_menu_item('page', $item);
	}
}

$items = Menus::getPageMenuItems($entity);
foreach ($items as $item) {
	elgg_register_menu_item('page', $item);
}

echo elgg_view_menu('page', [
	'entity' => $entity,
	'sort_by' => 'priority',
		]);
