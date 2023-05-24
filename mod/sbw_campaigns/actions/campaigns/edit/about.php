<?php

use SBW\Campaigns\Campaign;

elgg_make_sticky_form('campaigns/edit/about');

$user = elgg_get_logged_in_user_entity();

$guid = get_input('guid');

if ($guid) {
	$entity = get_entity($guid);
	if (!$entity instanceof Campaign) {
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
	if (!$container || !$container->canWriteToContainer(0, 'object', Campaign::SUBTYPE)) {
		$error = elgg_echo('campaigns:error:container_permissions');
		return elgg_error_response($error, REFERRER, ELGG_HTTP_FORBIDDEN);
	}

	$entity = new Campaign();
	$entity->owner_guid = $user->guid;
	$entity->container_guid = $container->guid;

	$action = 'create';
}

$title = get_input('title', '');
$description = get_input('description', '');
$briefdescription = get_input('briefdescription', '');
$rules = get_input('rules', '');

if (empty($title) || empty($description) || empty($briefdescription) || empty($rules)) {
	$error = elgg_echo('campaigns:error:required');
	return elgg_error_response($error, REFERRER, ELGG_HTTP_BAD_REQUEST);
}
$terms = get_input('terms');
if (empty($terms)) {
	$error = elgg_echo('campaigns:error:terms');
	return elgg_error_response($error, REFERRER, ELGG_HTTP_BAD_REQUEST);
}
$terms = time();

if (!$entity->guid || !$entity->started || elgg_is_admin_logged_in()) {
	$calendar_start = (int) get_input('calendar_start', '');
	$calendar_end = (int) get_input('calendar_end', '');
	if (empty($calendar_start) || empty($calendar_end)) {
		$error = elgg_echo('campaigns:error:required');
		return elgg_error_response($error, REFERRER, ELGG_HTTP_BAD_REQUEST);
	}

	$dt_ct = new DateTime();

	$cutoff_time = (int) elgg_get_plugin_setting('cutoff_time', 'sbw_campaigns', 12 * 3600);
	$dt_ct->setTimestamp($cutoff_time);

	$dt = new DateTime();

	$dt->setTimestamp($calendar_start);
	$dt->setTime($dt_ct->format('H'), $dt_ct->format('i'), $dt->format('s'));
	$calendar_start = $dt->getTimestamp();

	$dt->setTimestamp($calendar_end);
	$dt->setTime($dt_ct->format('H'), $dt_ct->format('i'), $dt->format('s'));
	$calendar_end = $dt->getTimestamp();

	if ($calendar_end <= $calendar_start) {
		$error = elgg_echo('campaigns:error:dates');
		return elgg_error_response($error, REFERRER, ELGG_HTTP_BAD_REQUEST);
	}

	$model = get_input('model');
	if (!in_array($model, [Campaign::MODEL_ALL_OR_NOTHING, Campaign::MODEL_MONEY_POT, Campaign::MODEL_RELIEF])) {
		$error = elgg_echo('campaigns:error:invalid');
		return elgg_error_response($error, REFERRER, ELGG_HTTP_BAD_REQUEST);
	}

	if ($model == Campaign::MODEL_RELIEF) {
		$target_amount = 0;
		$donation_minimum = 0;
		$currency = 'EUR';
	} else {
		$target_amount = (int) floor(get_input('target_amount'));
		$donation_minimum = (int) floor(get_input('donation_minimum'));
		$currency = get_input('currency');
		if (empty($target_amount) || empty($currency)) {
			$error = elgg_echo('campaigns:error:required');
			return elgg_error_response($error, REFERRER, ELGG_HTTP_BAD_REQUEST);
		}
	}
}

$access_id = (int) get_input('access_id', get_default_access());
$tags = get_input('tags', '');
$video_url = get_input('video_url', '');
$website = get_input('website', '');
$location = get_input('location', '');

$entity->title = htmlentities($title, ENT_QUOTES, 'UTF-8');
foreach ($description as $lang => $desc) {
	if ($lang == 'en') {
		$entity->description = $desc;
	} else {
		$entity->{"description_{$lang}"} = $desc;
	}
}

$entity->briefdescription = elgg_get_excerpt($briefdescription);
$entity->rules = $rules;
$entity->tags = string_to_tag_array($tags);
$entity->video_url = $video_url;
$entity->website = $website;
$entity->location = $location;
$entity->{'terms:campaigner'} = $terms;
$entity->access_id = $access_id;
$entity->future_access_id = $access_id;
$entity->relief_delivery = get_input('relief_delivery');

if (!$entity->guid || !$entity->started || elgg_is_admin_logged_in()) {
	$entity->model = $model;
	$entity->calendar_start = $calendar_start;
	$entity->calendar_end = $calendar_end;
	$entity->target_amount = \SebastianBergmann\Money\Money::fromString((string) $target_amount, $currency)->getAmount();
	$entity->donation_minimum = \SebastianBergmann\Money\Money::fromString((string) $donation_minimum, $currency)->getAmount();
	$entity->currency = $currency;
}

if ($entity->save()) {
	elgg_clear_sticky_form('campaigns/edit/about');
	$entity->saveIconFromUploadedFile('icon');

	if ($entity->owner_guid == $user->guid) {
		$managers = (array) get_input('managers', []);
		$manager_collection_members = (array) get_members_of_access_collection($entity->write_access_id, true);
		$manager_collection_members[] = $entity->owner_guid;

		$remove = array_diff($manager_collection_members, $managers);
		$add = array_diff($managers, $manager_collection_members);

		foreach ($remove as $manager_guid) {
			if ($manager_guid == $entity->owner_guid) {
				continue;
			}

			$manager = get_entity($manager_guid);
			remove_user_from_access_collection($manager_guid, $entity->write_access_id);

			if (!$manager) {
				continue;
			}

			$subject = elgg_echo('campaigns:remove_manager:notify:subject');
			$body = elgg_echo('campaigns:remove_manager:notify:body', [
				$manager->getDisplayName(),
				$user->getDisplayName(),
				$entity->getDisplayName(),
			]);
			notify_user($manager_guid, $user->guid, $subject, $body, [
				'action' => 'remove_manager',
				'object' => $entity,
			]);
		}

		foreach ($add as $manager_guid) {
			$manager = get_entity($manager_guid);
			if (!$manager) {
				continue;
			}
			add_user_to_access_collection($manager->guid, $entity->write_access_id);
			$methods = elgg_get_notification_methods();
			foreach ($methods as $method) {
				elgg_add_subscription($manager->guid, $method, $campaign->guid);
			}

			if ($manager->guid != elgg_get_logged_in_user_guid()) {
				$subject = elgg_echo('campaigns:add_manager:notify:subject');
				$body = elgg_echo('campaigns:add_manager:notify:body', [
					$manager->getDisplayName(),
					$user->getDisplayName(),
					$entity->getDisplayName(),
					$entity->getURL(),
				]);
				notify_user($manager_guid, $user->guid, $subject, $body, [
					'action' => 'add_manager',
					'object' => $entity,
				]);
			}
		}
	}

	$status = get_input('status');
	if ($status == 'published' && !$entity->isPublished()) {
		$entity->publish();
	}

	$data = [
		'entity' => $entity,
		'action' => $action,
	];

	if ($model == Campaign::MODEL_RELIEF) {
		$forward_url = "campaigns/edit/$entity->guid/relief_items";
	} else {
		$forward_url = $entity->getURL();
	}

	$message = elgg_echo('campaigns:success', [$entity->getDisplayName()]);
	return elgg_ok_response($data, $message, $forward_url);
}

$error = elgg_echo('campaigns:error:general');
return elgg_error_response($error, REFERRER, ELGG_HTTP_UNPROCESSABLE_ENTITY);
