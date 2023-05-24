<?php

$item = elgg_extract('item', $vars);

$object = $item->getObjectEntity();
if (!$object) {
	return;
}

$vars['message'] = elgg_get_excerpt($object->description);
$vars['attachments'] = elgg_view('campaigns/modules/stats', [
	'entity' => $object,
]);

echo elgg_view('river/elements/layout', $vars);
