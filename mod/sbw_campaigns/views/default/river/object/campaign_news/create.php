<?php

$item = elgg_extract('item', $vars);
/* @var $item ElggRiverItem */

$subject = $item->getSubjectEntity();
$object = $item->getObjectEntity();
$target = $item->getTargetEntity();

if (!$subject || !$object || !$target) {
	return;
}

$subject_link = elgg_view('output/url', array(
	'href' => $subject->getURL(),
	'text' => $subject->getDisplayname(),
	'class' => 'elgg-river-subject',
));

$object_link = elgg_view('output/url', array(
	'href' => $object->getURL(),
	'text' => elgg_get_excerpt($object->getDisplayName(), 100),
	'class' => 'elgg-river-object',
));

$target_link = elgg_view('output/url', array(
	'href' => $target->getURL(),
	'text' => $target->getDisplayName(),
	'class' => 'elgg-river-target',
));

$summary = elgg_echo('river:create:object:campaign_news', [$subject_link, $object_link, $target_link]);

$vars['summary'] = $summary;
$vars['message'] = elgg_get_excerpt($object->description);
$vars['attachments'] = elgg_view('campaigns/modules/stats', [
	'entity' => $object,
]);

echo elgg_view('river/elements/layout', $vars);