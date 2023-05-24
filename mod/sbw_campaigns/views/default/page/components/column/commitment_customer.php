<?php

$item = elgg_extract('item', $vars);
if (!$item instanceof ElggAnnotation) {
	return;
}

$owner = $item->getOwnerEntity();
if (!$owner) {
	return 'Unknown';
}

echo elgg_view('output/url', [
	'text' => $owner->getDisplayName(),
	'href' => $owner->getURL(),
]);
