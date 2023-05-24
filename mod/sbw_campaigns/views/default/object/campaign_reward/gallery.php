<?php

use SBW\Campaigns\Menus;
use SBW\Campaigns\Reward;

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof Reward) {
	return;
}

$icon = '';
$icon_size = elgg_extract('size', $vars, 'card');
if ($entity->hasIcon($icon_size)) {
	$icon = elgg_view_entity_icon($entity, $icon_size, [
		'href' => false,
		'use_link' => false,
	]);
}

$content = elgg_view('output/longtext', [
	'value' => $entity->description,
		]);

$subtitle = [];

$campaign = $entity->getContainerEntity();
$subtitle[] = $entity->getPrice()->format();

$subtitle[] = elgg_echo('campaigns:rewards:in_stock', [$entity->getStock()]);

$items = Menus::getRewardMenuItems($entity);
if ($items) {
	foreach ($items as &$item) {
		$item->addLinkClass('elgg-button elgg-button-action');
	}
}

$metadata = elgg_view_menu('campaign:reward', [
	'entity' => $entity,
	'items' => $items,
	'class' => 'elgg-menu-hz',
	'sort_by' => 'priority',
		]);

$title = $entity->getDisplayName();

$title = elgg_format_element('h3', [
	'class' => 'elgg-title',
		], $title);

$subtitle = elgg_format_element('div', [
	'class' => 'elgg-subtext',
		], implode('<br />', $subtitle));
?>
<div class="campaigns-card">
	<div class="campaigns-card-head elgg-head">
		<?= $metadata ?>
		<h3><?= $title ?></h3>
		<?= $subtitle ?>
	</div>
	<div class="campaigns-card-media">
		<?= $icon ?>
	</div>
	<div class="campaigns-card-body elgg-body">
		<?= $content ?>
	</div>
</div>