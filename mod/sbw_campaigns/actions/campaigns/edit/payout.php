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

$entity->payout_instructions = get_input('payout_instructions');

$entity->email = get_input('email');
$entity->first_name = get_input('first_name');
$entity->last_name = get_input('last_name');
$entity->company_name = get_input('company_name');
$entity->tax_id = get_input('tax_id');
$entity->phone = get_input('phone');

$data = [
	'entity' => $entity,
	'action' => 'payout',
];

$message = elgg_echo('campaigns:edit:payout:success');
return elgg_ok_response($data, $message);