<?php

use SBW\Campaigns\Commitment;

$entity = elgg_extract('item', $vars);

if (!$entity instanceof Commitment) {
	return;
}

$item_list = [];
$order = $entity->getOrder();
if ($order) {
	foreach ($order->all() as $item) {
		$item_list[] = strtolower("{$item->getQuantity()} $item->title");
	}
}

echo implode('<br />', $item_list);

