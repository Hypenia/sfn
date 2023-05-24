<?php

use hypeJunction\Payments\Amount;
use SBW\Campaigns\Campaign;
use SBW\Campaigns\Reward;

elgg_make_sticky_form('campaigns/edit/reward');

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
	if (!$container instanceof Campaign || !$container->canWriteToContainer(0, 'object', Reward::SUBTYPE)) {
		$error = elgg_echo('campaigns:error:container_permissions');
		return elgg_error_response($error, REFERRER, ELGG_HTTP_FORBIDDEN);
	}

	$entity = new Reward();
	$entity->owner_guid = $user->guid;
	$entity->container_guid = $container->guid;

	$action = 'create';
}

$title = get_input('title', '');
$description = get_input('description', '');
$donation_minimum = get_input('donation_minimum');
$quantity = (int) get_input('quantity');

if (empty($title) || empty($description)) {
	$error = elgg_echo('campaigns:error:required');
	return elgg_error_response($error, REFERRER, ELGG_HTTP_BAD_REQUEST);
}


$price = Amount::fromString($donation_minimum, $container->currency);
$donation_minimum = $price->getAmount();

if ($donation_minimum < $container->donation_minimum) {
	$error = elgg_echo('campaigns:error:reward_minimum_too_low', [$price->getConvertedAmount()]);
	return elgg_error_response($error, REFERRER, ELGG_HTTP_BAD_REQUEST);
}

if (!$quantity) {
	$error = elgg_echo('campaigns:error:quantity_too_low');
	return elgg_error_response($error, REFERRER, ELGG_HTTP_BAD_REQUEST);
}

$entity->title = htmlentities($title, ENT_QUOTES, 'UTF-8');
$entity->description = $description;
$entity->access_id = $container->access_id;
$entity->donation_minimum = (int) $donation_minimum;
$entity->setPrice($price);

$forward_url = "campaigns/edit/$container->guid/rewards";
if ($entity->save()) {

	$stock = $entity->getStock();
	if ($diff = $quantity - $stock) {
		$entity->addStock($diff);
	}

	elgg_clear_sticky_form('campaigns/edit/reward');
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
