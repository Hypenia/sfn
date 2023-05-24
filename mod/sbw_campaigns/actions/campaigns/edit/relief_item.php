<?php

use SBW\Campaigns\Campaign;
use SBW\Campaigns\ReliefItem;

elgg_make_sticky_form('campaigns/edit/relief_item');

$user = elgg_get_logged_in_user_entity();

$guid = get_input('guid');

if ($guid) {
	$entity = get_entity($guid);
	if (!$entity) {
		$error = elgg_echo('campaigns:error:not_found');
		return elgg_error_response($error, REFERRER, ELGG_HTTP_NOT_FOUND);
	}
	if (!$entity->canEdit()) {
		$error = elgg_echo('campaigns:error:permissions');
		return elgg_error_response($error, REFERRER, ELGG_HTTP_FORBIDDEN);
	}

	$container = $entity->getContainerEntity();

	$action = 'edit';
} else {
	$container_guid = get_input('container_guid');
	$container = get_entity($container_guid);
	if (!$container instanceof Campaign || !$container->canWriteToContainer(0, 'object', ReliefItem::SUBTYPE)) {
		$error = elgg_echo('campaigns:error:container_permissions');
		return elgg_error_response($error, REFERRER, ELGG_HTTP_FORBIDDEN);
	}

	$entity = new ReliefItem();
	$entity->owner_guid = $user->guid;
	$entity->container_guid = $container->guid;

	$action = 'create';
}

$title = get_input('title', '');
$description = get_input('description', '');
$required_quantity = (int) get_input('required_quantity');

if (empty($title) || empty($description)) {
	$error = elgg_echo('campaigns:error:required');
	return elgg_error_response($error, REFERRER, ELGG_HTTP_BAD_REQUEST);
}

if (!$required_quantity) {
	$error = elgg_echo('campaigns:error:quantity_too_low');
	return elgg_error_response($error, REFERRER, ELGG_HTTP_BAD_REQUEST);
}

$entity->title = htmlentities($title, ENT_QUOTES, 'UTF-8');
$entity->description = $description;
$entity->access_id = $container->access_id;
$entity->required_quantity = (int) $required_quantity;

$forward_url = "campaigns/edit/$container->guid/relief_items";
if ($entity->save()) {

	elgg_clear_sticky_form('campaigns/edit/relief_item');
	$entity->saveIconFromUploadedFile('icon');
	$data = [
		'entity' => $entity,
		'action' => $action,
	];
	$message = elgg_echo('campaigns:success', [$entity->getDisplayName()]);
	return elgg_ok_response($data, $message, $forward_url);
}

$error = elgg_echo('campaigns:error:general');
return elgg_error_response($error, $forward_url, ELGG_HTTP_UNPROCESSABLE_ENTITY);
