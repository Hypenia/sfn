<?php

namespace SBW\Campaigns;

use Elgg\Notifications\Notification;
use Elgg\Notifications\NotificationEvent;

class Notifications {

	/**
	 * Prepare a notification message about a started campaign
	 *
	 * @param string       $hook         Hook name
	 * @param string       $type         Hook type
	 * @param Notification $notification The notification to prepare
	 * @param array        $params       Hook parameters
	 * @return Notification
	 */
	public static function formatStartNotification($hook, $type, $notification, $params) {

		$entity = $params['event']->getObject();
		$container = $entity->getContainerEntity();

		$language = $params['language'];

		$end = date('Y-m-d', $entity->calendar_end);

		$notification->subject = elgg_echo('campaigns:start:notify:subject', array($entity->title), $language);
		$notification->body = elgg_echo('campaigns:start:notify:body', array(
			$entity->title,
			$container->name,
			$end,
			$entity->description,
			$entity->getURL()
				), $language);
		$notification->summary = elgg_echo('campaigns:start:notify:summary', array($entity->title, $end), $language);

		return $notification;
	}

	/**
	 * Prepare a notification message about a campaign milestone
	 *
	 * @param string       $hook         Hook name
	 * @param string       $type         Hook type
	 * @param Notification $notification The notification to prepare
	 * @param array        $params       Hook parameters
	 * @return Notification
	 */
	public static function formatMilestoneNotification($hook, $type, $notification, $params) {

		$entity = $params['event']->getObject();
		$container = $entity->getContainerEntity();

		$language = $params['language'];

		$end = date('Y-m-d', $entity->calendar_end);

		$notification->subject = elgg_echo('campaigns:milestone:notify:subject', array($entity->title, "{$entity->funded_percentage}%"), $language);
		$notification->body = elgg_echo('campaigns:milestone:notify:body', array(
			$entity->title,
			$container->name,
			"{$entity->funded_percentage}%",
			$end,
			$entity->description,
			$entity->getURL()
				), $language);
		$notification->summary = elgg_echo('campaigns:milestone:notify:summary', array($entity->title, "{$entity->funded_percentage}%"), $language);

		return $notification;
	}

	/**
	 * Prepare a notification message about a campaign end
	 *
	 * @param string       $hook         Hook name
	 * @param string       $type         Hook type
	 * @param Notification $notification The notification to prepare
	 * @param array        $params       Hook parameters
	 * @return Notification
	 */
	public static function formatEndNotification($hook, $type, $notification, $params) {

		$entity = $params['event']->getObject();
		$container = $entity->getContainerEntity();

		$language = $params['language'];

		$notification->subject = elgg_echo('campaigns:end:notify:subject', array($entity->title, "{$entity->funded_percentage}%"), $language);
		$notification->body = elgg_echo('campaigns:end:notify:body', array(
			$entity->title,
			$container->name,
			"{$entity->funded_percentage}%",
			$entity->description,
			$entity->getURL()
				), $language);
		$notification->summary = elgg_echo('campaigns:end:notify:summary', array($entity->title, "{$entity->funded_percentage}%"), $language);

		return $notification;
	}

	/**
	 * Prepare a notification message about campaign news
	 *
	 * @param string       $hook         Hook name
	 * @param string       $type         Hook type
	 * @param Notification $notification The notification to prepare
	 * @param array        $params       Hook parameters
	 * @return Notification
	 */
	public static function formatNewsNotification($hook, $type, $notification, $params) {

		$entity = $params['event']->getObject();

		$campaign = $entity->getContainerEntity();

		$language = $params['language'];

		$notification->subject = elgg_echo('campaigns:news:notify:subject', array($campaign->getDisplayName()), $language);
		$notification->body = elgg_echo('campaigns:news:notify:body', array(
			$campaign->name,
			$entity->title,
			$entity->description,
			$entity->getURL()
				), $language);

		return $notification;
	}

	/**
	 * Prepare a notification message about campaign news
	 *
	 * @param string       $hook         Hook name
	 * @param string       $type         Hook type
	 * @param Notification $notification The notification to prepare
	 * @param array        $params       Hook parameters
	 * @return Notification
	 */
	public static function formatDonationNotification($hook, $type, $notification, $params) {

		$entity = $params['event']->getObject();
		/* @var $entity Donation */

		$campaign = $entity->getContainerEntity();

		$language = $params['language'];

		$notification->subject = elgg_echo('campaigns:donation:notify:subject', array($campaign->getDisplayName()), $language);

		if ($entity->anonymous) {
			$notification->body = elgg_echo('campaigns:donation:notify:anonymous:body', array(
				$campaign->name,
				$entity->getNetAmount()->format(),
				"{$entity->funded_percentage}%",
				$campaign->getURL(),
					), $language);
		} else {
			$notification->body = elgg_echo('campaigns:donation:notify:body', array(
				$campaign->name,
				$entity->name,
				$entity->getNetAmount()->format(),
				"{$entity->funded_percentage}%",
				$campaign->getURL(),
					), $language);
		}

		return $notification;
	}

	/**
	 * Populate subscribers list with users subscribed to the campaign
	 *
	 * @param string $hook   Hook name
	 * @param string $type   Hook type
	 * @param arrau  $return Subscribers
	 * @param array  $params Hook parameters
	 * @return array
	 */
	public static function getSubscriptions($hook, $type, $return, $params) {

		$event = elgg_extract('event', $params);
		/* @var $event NotificationEvent */

		$object = $event->getObject();

		if ($object instanceof Donation && $object->anonymous) {
			// Do not notify friends/subscriers about anonymous donations
			// Only campaign subscribers receive a notification about an anonymous donation
			$return = [];
		}

		if (!$object instanceof Campaign && $object instanceof ElggObject) {
			// Notify campaigns subscribers about donations, news items etc contained by the campaign
			$object = $object->getContainerEntity();
		}

		if ($object instanceof Campaign) {
			$dbprefix = elgg_get_config('dbprefix');
			$query = "
			SELECT guid_one AS guid,
			GROUP_CONCAT(relationship SEPARATOR ',') AS methods
			FROM {$dbprefix}entity_relationships
			WHERE guid_two = :guid_two AND
				  relationship LIKE 'notify%' GROUP BY guid_one
			";

			$records = get_data($query, null, [
				':guid_two' => (int) $object->guid,
			]);

			foreach ($records as $record) {
				$deliveryMethods = explode(',', $record->methods);
				$return[$record->guid] = substr_replace($deliveryMethods, '', 0, 6);
			}

			if (in_array($event->getAction(), ['start', 'end', 'milestone'])) {
				// Always notify admins about campaign start and end
				$admins = elgg_get_admins([
					'limit' => 0,
					'batch' => true,
				]);

				foreach ($admins as $admin) {
					if (!array_key_exists($admin->guid, $return)) {
						$return[$admin->guid] = ['email'];
					}
				}
			}
		}

		return $return;
	}

}
