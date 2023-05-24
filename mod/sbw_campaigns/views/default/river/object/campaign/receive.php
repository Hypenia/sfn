<?php

$item = elgg_extract('item', $vars);

$subject = $item->getSubjectEntity();
$object = $item->getObjectEntity();
if (!$object || !$subject) {
	return;
}

$subject_link = elgg_view('output/url', array(
	'href' => $subject->getURL(),
	'text' => $subject->title,
	'class' => 'elgg-river-subject',
	'is_trusted' => true,
		));

$campaign = $object->getContainerEntity();
$amount = $object->getNetAmount()->format();

if ($object->anonymous) {
	$vars['summary'] = elgg_echo('river:campaigns:receive:anonymous', [$subject_link, $amount]);
} else {
	$vars['summary'] = elgg_echo('river:campaigns:receive:guest', [$subject_link, $amount, $object->name]);
}

$vars['message'] = elgg_view('output/longtext', [
	'value' => $campaign->briefdescription,
		]);

$vars['attachments'] = elgg_view('campaigns/modules/river', [
	'entity' => $campaign,
		]);

echo elgg_view('river/elements/layout', $vars);
