<?php

use SBW\Campaigns\Reward;
use SebastianBergmann\Money\Currency;
use SebastianBergmann\Money\Money;

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof Reward) {
	return;
}

$icon_size = elgg_extract('size', $vars, 'small');
$icon = elgg_view_entity_icon($entity, $icon_size, [
	'href' => false,
	'use_link' => false,
		]);

$content = elgg_view('output/longtext', [
	'value' => $entity->description,
		]);

$subtitle = [];
$campaign = $entity->getContainerEntity();

$currency = new Currency($entity->currency);
$donation_minimum = (new Money($entity->donation_minimum, $currency))->getConvertedAmount();
$subtitle[] = elgg_echo('campaigns:rewards:donation_minimum', [$donation_minimum, $currency]);

$subtitle[] = elgg_echo('campaigns:rewards:in_stock', [$entity->getStock()]);

$metadata = '';
if (!elgg_in_context('metadata')) {
	$params = $vars;
	$params['sort_by'] = 'priority';
	$params['class'] = 'elgg-menu-hz';
	$metadata = elgg_view_menu('entity', $params);
}

$summary = elgg_view('object/elements/summary', [
	'entity' => $entity,
	'title' => $entity->getDisplayName(),
	'subtitle' => implode('<br />', $subtitle),
	'content' => $content,
	'metadata' => $metadata,
		]);

echo elgg_view_image_block($icon, $summary);
