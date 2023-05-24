<?php

namespace hypeJunction\Discovery;

use ElggEntity;

$entity = elgg_extract('entity', $vars);
$share_url = elgg_extract('share_url', $vars);

echo elgg_view_menu('discovery_share', [
	'entity' => $entity,
	'share_url' => $share_url,
	'class' => 'discovery-buttonbank elgg-menu-hz',
	'sort_by' => 'priority',
]);

if ($entity instanceof ElggEntity) {
	$permalink = get_entity_permalink($entity);
	$label = elgg_echo('discovery:entity:permalink');
} else {
	$permalink = $share_url;
	$label = elgg_echo('discovery:share_url');
}

if ($permalink) {
	echo elgg_view_input('text', array(
		'value' => $permalink,
		'label' => $label,
	));
	
	if (is_embeddable($entity)) {
		$response = elgg_trigger_plugin_hook('export:entity', 'oembed', array(
			'origin' => $permalink,
			'entity' => $entity,
			'maxwidth' => elgg_extract('maxwidth', $vars, 640),
			'maxheight' => elgg_extract('maxheight', $vars, 480),
		));

		if (!empty($response['html'])) {
			echo elgg_view_input('text', array(
				'value' => $response['html'],
				'label' => elgg_echo('discovery:entity:embed_code'),
			));
		}
	}
}