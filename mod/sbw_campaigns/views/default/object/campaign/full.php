<?php

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \SBW\Campaigns\Campaign) {
	return;
}

$content = elgg_view('campaigns/profile', $vars);

$body = elgg_format_element('div', [
	'class' => 'campaigns-full-listing',
		], $content);

echo elgg_view('object/elements/full', [
	'entity' => $entity,
	'summary' => '',
	'body' => $body,
	'icon' => '',
]);
