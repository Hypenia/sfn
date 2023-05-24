<?php

use SBW\Campaigns\Campaign;

$entity = elgg_extract('entity', $vars);

if (!$entity instanceof Campaign) {
	return;
}

echo elgg_format_element('div', ['id' => 'campaigns-page-start']);

$tabs = [];

$media = elgg_view('campaigns/modules/media', $vars);

$about = elgg_view('campaigns/modules/about', $vars);
if ($about) {
	$tabs[] = [
		'text' => elgg_echo('campaigns:about'),
		'content' => $about,
		'selected' => true,
	];
}

$comments = elgg_view_comments($entity);
if ($comments) {
	$tabs[] = [
		'text' => elgg_echo('comments'),
		'content' => $comments,
	];
}

$news = elgg_view('campaigns/modules/news', $vars);
if ($news) {
	$tabs[] = [
		'text' => elgg_echo('campaigns:news'),
		'content' => $news,
	];
}

if ($entity->model == Campaign::MODEL_RELIEF) {
	$commitments = elgg_view('campaigns/modules/commitments', $vars);
	if ($commitments) {
		$tabs[] = [
			'text' => elgg_echo('campaigns:commitments'),
			'content' => $commitments,
		];
	}
} else {
	$donations = elgg_view('campaigns/modules/donations', $vars);
	if ($donations) {
		$tabs[] = [
			'text' => elgg_echo('campaigns:donations'),
			'content' => $donations,
		];
	}
}

echo $media;

echo elgg_view('page/components/tabs', [
	'tabs' => $tabs,
]);

$items = \SBW\Campaigns\Menus::getProfileMenuItems($entity);
foreach ($items as &$item) {
	$item->addLinkClass('elgg-button elgg-button-action');
}

$items[] = [
	'name' => 'page-start',
	'href' => '#campaigns-page-start',
	'text' => elgg_view_icon('arrow-up') . elgg_echo('campaigns:page-start'),
	'priority' => 1,
	'link_class' => 'elgg-button elgg-button-action',
];

echo elgg_view_menu('campaign:actions', [
	'items' => $items,
	'class' => 'elgg-menu-hz',
	'sort_by' => 'priority',
]);