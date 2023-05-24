<?php

elgg_admin_gatekeeper();

$guid = get_input('guid');
$entity = get_entity($guid);

if (!$entity instanceof SBW\Campaigns\Campaign) {
	$error = elgg_echo('campaigns:error:not_found');
	return elgg_error_response($error, REFERRER, ELGG_HTTP_NOT_FOUND);
}

if ($entity->isVerified()) {
	$error = elgg_echo('campaigns:error:already_verified');
	return elgg_error_response($error, REFERRER, ELGG_HTTP_BAD_REQUEST);
}

if ($entity->verify()) {
	$data = [
		'entity' => $entity,
		'action' => 'verify',
	];
	$message = elgg_echo('campaigns:verify:success', [$entity->getDisplayName()]);
	return elgg_ok_response($data, $message);
}

$error = elgg_echo('campaigns:error:general');
return elgg_error_response($error, REFERRER, ELGG_HTTP_UNPROCESSABLE_ENTITY);
