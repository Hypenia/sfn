<?php

use Elgg\Http\ResponseBuilder;
use hypeJunction\Payments\Transaction;

$guid = get_input('guid');
$entity = get_entity($guid);

if (!$entity instanceof Transaction) {
	$error = elgg_echo('payments:error:not_found');
	return elgg_error_response($error, REFERRER, ELGG_HTTP_NOT_FOUND);
}

if (!$entity->canEdit()) {
	$error = elgg_echo('payments:error:permissions');
	return elgg_error_response($error, REFERRER, ELGG_HTTP_FORBIDDEN);
}

$result = $entity->refund();

if (!$result) {
	$error = elgg_echo('payments:refund:error');
	return elgg_error_response($error, REFERRER, ELGG_HTTP_UNPROCESSABLE_ENTITY);
}

if ($result instanceof ResponseBuilder) {
	return $result;
}

$data = [
	'entity' => 'transaction',
	'action' => 'refund',
];
$message = elgg_echo('payments:refund:success');
return elgg_ok_response($data, $message);