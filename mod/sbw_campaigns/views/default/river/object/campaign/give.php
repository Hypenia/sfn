<?php

$item = elgg_extract('item', $vars);

$subject = $item->getSubjectEntity();
$object = $item->getObjectEntity();
if (!$object || !$subject) {
	return;
}

$subject_link = elgg_view('output/url', array(
	'href' => $subject->getURL(),
	'text' => $subject->name,
	'class' => 'elgg-river-subject',
	'is_trusted' => true,
		));

if ($object instanceof SBW\Campaigns\Donation) {
	$campaign = $object->getContainerEntity();
	$amount = $object->getNetAmount()->format();

	$object_link = elgg_view('output/url', array(
		'href' => $campaign->getURL(),
		'text' => $campaign->title,
		'class' => 'elgg-river-object',
	));

	$vars['summary'] = elgg_echo('river:campaigns:give', [$subject_link, $amount, $object_link]);
} else {
	$campaign = $object;
	$object_link = elgg_view('output/url', array(
		'href' => $object->getURL(),
		'text' => $object->title,
		'class' => 'elgg-river-object',
	));
	
	$vars['summary'] = elgg_echo('river:give:object:default', [$subject_link, $object_link]);
}

$vars['message'] = elgg_view('output/longtext', [
	'value' => $object->getUnserializedMetadata('comment'),
		]);

$vars['attachments'] = elgg_view('campaigns/modules/river', [
	'entity' => $campaign,
		]);

echo elgg_view('river/elements/layout', $vars);
