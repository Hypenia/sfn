<?php

$guid = get_input('guid');
$entity = get_entity($guid);

if (!$entity instanceof SBW\Campaigns\Campaign) {
	$error = elgg_echo('campaigns:error:not_found');
	return elgg_error_response($error, REFERRER, ELGG_HTTP_NOT_FOUND);
}

if (!elgg_is_admin_logged_in()) {
	$error = elgg_echo('campaigns:error:permissions');
	return elgg_error_response($error, REFERRER, ELGG_HTTP_FORBIDDEN);
}

if ($entity->started) {
	$error = elgg_echo('campaigns:error:already_started');
	return elgg_error_response($error, REFERRER, ELGG_HTTP_BAD_REQUEST);
}

if ($entity->start()) {
	$data = [
		'entity' => $entity,
		'action' => 'start',
	];
	$message = elgg_echo('campaigns:start:success', [$entity->getDisplayName()]);
	return elgg_ok_response($data, $message);
}

$error = elgg_echo('campaigns:error:general');
return elgg_error_response($error, REFERRER, ELGG_HTTP_UNPROCESSABLE_ENTITY);
