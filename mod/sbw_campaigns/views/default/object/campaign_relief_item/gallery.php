<?php

use SBW\Campaigns\Menus;
use SBW\Campaigns\ReliefItem;

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof ReliefItem) {
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

$campaign = $entity->getContainerEntity();

$items = Menus::getReliefItemMenuItems($entity);
if ($items) {
	foreach ($items as &$item) {
		$item->addLinkClass('elgg-button elgg-button-action');
	}
}

$metadata = elgg_view_menu('campaign:relief_item', [
	'entity' => $entity,
	'items' => $items,
	'class' => 'elgg-menu-hz',
	'sort_by' => 'priority',
		]);

$title = $entity->getDisplayName();

$title = elgg_format_element('h3', [
	'class' => 'elgg-title',
		], $title);

$stats = elgg_view('object/campaign_relief_item/stats', $vars);
?>
<div class="campaigns-card">
	<div class="campaigns-card-head elgg-head">
		<?= $metadata ?>
		<h3><?= $title ?></h3>
	</div>
	<?= $stats ?>
	<div class="campaigns-card-media">
		<?= $icon ?>
	</div>
	<div class="campaigns-card-body elgg-body">
		<?= $content ?>
	</div>
</div>