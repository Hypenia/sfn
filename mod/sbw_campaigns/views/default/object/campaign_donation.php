<?php

use SBW\Campaigns\Donation;

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof Donation) {
	return;
}

$icon = '';
if ($entity->anonymous) {
	$title = elgg_echo('campaigns:anonymous');
} else {
	$users = get_user_by_email($entity->email);
	if ($users) {
		$user = array_shift($users);
		$title = elgg_view('output/url', [
			'href' => $user->getURL(),
			'text' => $user->getDisplayName(),
			'target' => '_blank',
		]);
		$icon = elgg_view_entity_icon($user, 'small');
	} else {
		$title = $entity->name;
	}
}

$title = elgg_format_element('h3', [
	'class' => 'campaigns-donation-name',
		], $title);

$amount = elgg_format_element('div', [
	'class' => 'campaigns-donation-amount',
		], $entity->getNetAmount()->format());

$comment = $entity->getUnserializedMetadata('comment');
if ($comment) {
	$comment = elgg_view('output/longtext', [
		'value' => $comment,
	]);
	$comment = elgg_format_element('blockquote', [], $comment);
}

echo elgg_view('object/elements/summary', [
	'entity' => $entity,
	'title' => $title,
	'metadata' => $amount,
	'tags' => false,
	'content' => $comment,
	'icon' => $icon,
]);
