<?php

use SBW\Campaigns\Campaign;

$guid = get_input('guid');
$entity = get_entity($guid);

if (!$entity instanceof Campaign) {
	$error = elgg_echo('campaigns:error:not_found');
	return elgg_error_response($error, REFERRER, ELGG_HTTP_NOT_FOUND);
}

$user = elgg_get_logged_in_user_entity();
$methods = elgg_get_notification_methods();

foreach ($methods as $method) {
	var_dump(elgg_remove_subscription($user->guid, $method, $entity->guid));
}

$data = [
	'entity' => $entity,
	'action' => 'follow',
];
$message = elgg_echo('campaigns:unfollow:success', [$entity->getDisplayName()]);

return elgg_ok_response($data, $message);