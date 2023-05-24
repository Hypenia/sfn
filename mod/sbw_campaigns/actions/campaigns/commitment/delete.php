<?php

use SBW\Campaigns\Campaign;
use SBW\Campaigns\Commitments;

$id = get_input('id');
$annotation = elgg_get_annotation_from_id($id);

if (!$annotation instanceof ElggAnnotation) {
	$error = elgg_echo('campaigns:error:not_found');
	return elgg_error_response($error, REFERRER, ELGG_HTTP_NOT_FOUND);
}

$entity = $annotation->getEntity();
if ($entity) {
	$campaign = $entity->getContainerEntity();
}
if ($annotation->delete()) {
	if ($campaign instanceof Campaign) {
		Commitments::updateStats($campaign);
	}
	$message = elgg_echo('campaigns:commitment:delete:success');
	return elgg_ok_response($message);
} else {
	$message = elgg_echo('campaigns:commitment:delete:error');
	return elgg_error_response($message);
}