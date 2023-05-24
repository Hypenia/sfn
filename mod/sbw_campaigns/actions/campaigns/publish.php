<?php

$guid = get_input('guid');
$entity = get_entity($guid);

if (!$entity instanceof SBW\Campaigns\Campaign) {
	$error = elgg_echo('campaigns:error:not_found');
	return elgg_error_response($error, REFERRER, ELGG_HTTP_NOT_FOUND);
}

if (!$entity->canEdit()) {
	$error = elgg_echo('campaigns:error:permissions');
	return elgg_error_response($error, REFERRER, ELGG_HTTP_FORBIDDEN);
}

//$rewards = $entity->getRewards(['count' => true]);
//if (!$rewards) {
//	unset($entity->published);
//	$error = elgg_echo('campaigns:error:publish_without_rewards');
//	return elgg_error_response($error, REFERRER, ELGG_HTTP_UNPROCESSABLE_ENTITY);
//}

if ($entity->isPublished()) {
	$error = elgg_echo('campaigns:error:already_published');
	return elgg_error_response($error, REFERRER, ELGG_HTTP_BAD_REQUEST);
}

if ($entity->publish()) {
	$data = [
		'entity' => $entity,
		'action' => 'publish',
	];
	$message = elgg_echo('campaigns:publish:success', [$entity->getDisplayName()]);
	return elgg_ok_response($data, $message);
}

$error = elgg_echo('campaigns:error:general');
return elgg_error_response($error, REFERRER, ELGG_HTTP_UNPROCESSABLE_ENTITY);
