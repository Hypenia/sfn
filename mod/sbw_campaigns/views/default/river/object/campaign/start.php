<?php

$item = elgg_extract('item', $vars);

$object = $item->getObjectEntity();
if (!$object) {
	return;
}

$vars['message'] = elgg_view('output/longtext', [
	'value' => $object->briefdescription,
		]);

$vars['attachments'] = elgg_view('campaigns/modules/river', [
	'entity' => $object,
		]);

echo elgg_view('river/elements/layout', $vars);
