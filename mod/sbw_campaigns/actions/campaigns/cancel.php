<?php

use hypeJunction\Payments\SessionStorage;
use SBW\Campaigns\Campaign;

$guid = get_input('guid');
$campaign = get_entity($guid);

if (!$campaign instanceof Campaign) {
	$error = elgg_echo('campaigns:error:not_found');
	return elgg_error_response($error, REFERRER, ELGG_HTTP_NOT_FOUND);
}

$storage = new SessionStorage();
$storage->invalidate($campaign->guid);

forward($campaign->getURL());