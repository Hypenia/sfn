<?php

$subject = elgg_extract('subject', $vars, 'campaigner');

elgg_push_breadcrumb(elgg_echo('campaigns'), '/campaigns');
elgg_push_breadcrumb(elgg_echo('campaigns:terms:campaigner'), '/terms/campaigner');

switch ($subject) {
	case 'campaigner' :
		$title = elgg_echo('campaigns:terms:campaigner');
		$content = elgg_view('output/longtext', [
			'value' => elgg_get_plugin_setting('terms:campaigner', 'sbw_campaigns', ''),
		]);
		break;

	case 'donor' :
		$title = elgg_echo('campaigns:terms:donor');
		$content = elgg_view('output/longtext', [
			'value' => elgg_get_plugin_setting('terms:donor', 'sbw_campaigns', ''),
		]);
		break;

	case 'campaign' :
		$guid = get_input('guid');
		$campaign = get_entity($guid);
		if (!$campaign instanceof SBW\Campaigns\Campaign) {
			forweard('', '404');
		}
		$sidebar = elgg_view('campaigns/sidebars/owner_block', $vars);
		$title = elgg_echo('campaigns:terms:campaign', [$campaign->getDisplayName()]);
		$content = elgg_view('output/longtext', [
			'value' => $campaign->rules,
		]);
		break;

	default :
		forward('', '404');
		break;
}

if (elgg_is_xhr()) {
	echo elgg_view_module('lightbox', $title, $content);
	return;
}

$layout = elgg_view_layout('campaign', $vars + [
	'title' => $title,
	'content' => $content,
]);

echo elgg_view_page($title, $layout, 'default', $vars);