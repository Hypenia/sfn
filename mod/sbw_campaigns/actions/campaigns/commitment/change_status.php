<?php

$guid = get_input('guid');

$entity = get_entity($guid);
	if (!$entity instanceof \SBW\Campaigns\Commitment) {
		$error = elgg_echo('campaigns:error:not_found');
		return elgg_error_response($error, REFERRER, ELGG_HTTP_NOT_FOUND);
	}
	if (!$entity->canEdit()) {
		$error = elgg_echo('campaigns:error:permissions');
		return elgg_error_response($error, REFERRER, ELGG_HTTP_FORBIDDEN);
	}

$status = get_input('status');
$entity->setStatus($status);

$message = elgg_echo('campaigns:commitment:change_status:success');
return elgg_ok_response('', $message, $entity->getURL());

