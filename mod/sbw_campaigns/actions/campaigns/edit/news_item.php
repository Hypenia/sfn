<?php

use hypeJunction\Payments\Amount;
use SBW\Campaigns\Campaign;
use SBW\Campaigns\NewsItem;

elgg_make_sticky_form('campaigns/edit/news_item');

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
	if (!$container instanceof Campaign || !$container->canWriteToContainer(0, 'object', NewsItem::SUBTYPE)) {
		$error = elgg_echo('campaigns:error:container_permissions');
		return elgg_error_response($error, REFERRER, ELGG_HTTP_FORBIDDEN);
	}

	$entity = new NewsItem();
	$entity->owner_guid = $user->guid;
	$entity->container_guid = $container->guid;

	$action = 'create';
}

$title = get_input('title', '');
$description = get_input('description', '');

if (empty($title) || empty($description)) {
	$error = elgg_echo('campaigns:error:required');
	return elgg_error_response($error, REFERRER, ELGG_HTTP_BAD_REQUEST);
}

$entity->title = htmlentities($title, ENT_QUOTES, 'UTF-8');
$entity->description = $description;
$entity->access_id = $container->access_id;

if ($entity->save()) {

	elgg_clear_sticky_form('campaigns/edit/news_item');
	//$entity->saveIconFromUploadedFile('icon');

	$data = [
		'entity' => $entity,
		'action' => $action,
	];

	if ($action == 'create') {
		elgg_create_river_item([
			'view' => 'river/object/campaign_news/create',
			'action_type' => 'create',
			'subject_guid' => $entity->owner_guid,
			'object_guid' => $entity->guid,
			'target_guid' => $entity->container_guid,
		]);
	}
	
	$message = elgg_echo('campaigns:success', [$entity->getDisplayName()]);
	$forward_url = "campaigns/edit/$container->guid/news";
	return elgg_ok_response($data, $message, $forward_url);
}

$error = elgg_echo('campaigns:error:general');
return elgg_error_response($error, REFERRER, ELGG_HTTP_UNPROCESSABLE_ENTITY);
