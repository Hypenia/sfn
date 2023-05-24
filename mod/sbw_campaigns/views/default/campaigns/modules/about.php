<?php

use SBW\Campaigns\Campaign;

$entity = elgg_extract('entity', $vars);

if (!$entity instanceof Campaign) {
	return;
}

$tabs = [];
$languages = array_keys(get_installed_translations());
foreach ($languages as $lang) {
	if ($lang == 'en') {
		$value = $entity->description;
	} else {
		$value = $entity->{"description_{$lang}"};
	}
	if (!$value) {
		continue;
	}

	if (elgg_view_exists("language_selector/flags/$lang.gif")) {
		$label = elgg_view('output/img', [
			'src' => elgg_get_simplecache_url("language_selector/flags/$lang.gif"),
			'alt' => elgg_echo($lang),
		]);
	} else {
		$label = elgg_echo($lang);
	}

	$tabs[] = [
		'text' => $label,
		'content' => elgg_view('output/longtext', [
			'value' => $value,
		]),
		'selected' => get_current_language() == $lang,
	];
}

if (!empty($tabs)) {
	echo elgg_view('page/components/tabs', [
		'tabs' => $tabs,
	]);
}

$links[] = [
	'name' => 'terms:campaign',
	'target' => '_blank',
	'href' => 'campaigns/terms/campaign?guid=' . $entity->guid,
	'text' => elgg_echo('campaigns:terms:campaign', [$entity->getDisplayName()]),
	'class' => 'elgg-lightbox',
];

$terms = elgg_get_plugin_setting('terms:donor', 'sbw_campaigns');
if ($terms) {
	$links[] = [
		'name' => 'terms:donor',
		'target' => '_blank',
		'href' => 'campaigns/terms/donor',
		'text' => elgg_echo('campaigns:terms:donor'),
		'class' => 'elgg-lightbox',
	];
}

echo elgg_view_menu('campaigns:about:rules', [
	'items' => $links,
]);