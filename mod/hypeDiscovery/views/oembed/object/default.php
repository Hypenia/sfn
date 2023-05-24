<?php

namespace hypeJunction\Discovery;

$entity = elgg_extract('entity', $vars);

if (!elgg_instanceof($entity)) {
	return;
}

$sizes = elgg_get_icon_sizes($entity->getType(), $entity->getSubtype(), 'open_graph_image');

$maxwidth = get_input('maxwidth', 640);
$maxheight = get_input('maxheight', 480);

$size = 'medium';
if ($maxwidth > $sizes[$size]['w'] || $maxheight > $sizes[$size]['h']) {
	$size = 'large';
}

$background = $entity->getIconURL([
	'size' => 'large',
	'type' => 'open_graph_image',
]);

$byline = '';
$owner = $entity->getOwnerEntity();
if (is_discoverable($owner)) {
	$owner_icon = $owner->getIconURL([
		'size' => 'small',
		'type' => 'open_graph_image',
	]);
	$owner_name = $owner->getDisplayName();
	$owner_url = $owner->getURL();
	$owner_link = elgg_view('output/url', [
		'text' => $owner_name,
		'href' => $owner_url,
	], false, false, 'default');

	$byline = elgg_echo('byline', [$owner_link]);
}

$url = get_entity_permalink($entity);
$title = get_discovery_title($entity);
$description = elgg_get_excerpt(get_discovery_description($entity));

$icon = elgg_view('framework/discovery/icon', array(
	'entity' => $entity,
	'size' => '_og',
	'img_class' => 'elgg-photo'
));

?>
<div class="elgg-oembed-card" style="background-image:url(<?= $background ?>)">
	<div class="elgg-oembed-attributes">
		<div class="elgg-oembed-title">
			<a href="<?= $url ?>" /><?= $title ?></a>
		</div>
		<div class="elgg-oembed-description">
			<?= $description ?>
		</div>
		<?php
		if ($owner_name) {
			?>
			<img class="elgg-oembed-owner-icon" src="<?= $owner_icon ?>" />
			<div class="elgg-oembed-byline">
				<?= $byline ?>
			</div>
			<?php
		}
		?>
	</div>
</div>