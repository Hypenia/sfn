<?php

use SBW\Campaigns\Campaign;
use SBW\Campaigns\Menus;

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof Campaign) {
	return;
}

if ($entity->video_url) {
	$player = elgg_view('output/player', [
		'href' => $entity->video_url,
		'fallback' => false,
	]);
}

if ($player) {
	$media = elgg_format_element('div', [
		'class' => 'scraper-card-flex',
			], $player);
} else {
	$media_size = elgg_extract('size', $vars, 'card');
	$media = elgg_view_entity_icon($entity, $media_size);
}

$content = elgg_view('output/longtext', [
	'value' => $entity->briefdescription,
		]);

$subtitle = [];
$container = $entity->getContainerEntity();
if ($container) {
	$link = elgg_view('output/url', [
		'href' => $container->getURL(),
		'text' => $container->getDisplayName(),
	]);
	$subtitle[] = elgg_echo('byline', [$link]);
}

if (!$entity->isPublished()) {
	$status = elgg_format_element('span', [
		'class' => 'campaigns-status campaigns-state-inactive',
			], elgg_echo('campaigns:status:draft'));
} else if (!$entity->isVerified()) {
	$status = elgg_format_element('span', [
		'class' => 'campaigns-status campaigns-state-inactive',
			], elgg_echo('campaigns:status:pending_verification'));
} else if ($entity->isActive()) {
	$status = elgg_format_element('span', [
		'class' => 'campaigns-status campaigns-state-active',
			], elgg_echo('campaigns:status:ongoing'));
} else if ($entity->ended) {
	$status = elgg_format_element('span', [
		'class' => 'campaigns-status campaigns-state-inactive',
			], elgg_echo('campaigns:status:ended'));
} else if (!$entity->started) {
	$status = elgg_format_element('span', [
		'class' => 'campaigns-status campaigns-state-inactive',
			], elgg_echo('campaigns:status:starting_soon'));
}

$stats = elgg_view('campaigns/modules/stats', [
	'entity' => $entity,
]);

$title = elgg_view('output/url', [
	'text' => $entity->getDisplayName(),
	'href' => $entity->getURL(),
		]);

$title = elgg_format_element('h3', [
	'class' => 'elgg-title',
		], $title);

$subtitle = elgg_format_element('div', [
	'class' => 'elgg-subtext',
		], implode('<br />', $subtitle));

$data = elgg_view('campaigns/modules/data', $vars);

$items = Menus::getProfileMenuItems($entity);
foreach ($items as &$item) {
	$item->addLinkClass('elgg-button elgg-button-action');
}

$metadata = elgg_view_menu('campaign:profile', [
	'entity' => $entity,
	'items' => $items,
	'class' => 'elgg-menu-hz',
	'sort_by' => 'priority',
		]);

?>
<div class="campaigns-card">
	<div class="campaigns-card-head elgg-head">
		<?= $status ?>
		<h3><?= $title ?></h3>
		<?= $subtitle ?>
	</div>
	<div class="campaigns-card-media">
		<?= $media ?>
	</div>
	<div class="campaigns-card-body elgg-body">
		<?= $content ?>
	</div>
	<div class="campaigns-card-data">
		<?= $data ?>
	</div>
	<div class="campaigns-card-stats">
		<?= $stats ?>
	</div>
	<div class="campaigns-actions">
		<?= $metadata ?>
	</div>
</div>
