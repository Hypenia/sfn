<?php

namespace SBW\Campaigns;

use ElggBatch;
use ElggEntity;
use hypeJunction\Payments\Transaction;

class Permissions {

	/**
	 * Check editing permissions
	 * 
	 * @param string $hook   "permissions_check"
	 * @param string $type   "object"
	 * @param bool   $return Permission
	 * @param array  $params Hook params
	 * @return bool
	 */
	public static function canEdit($hook, $type, $return, $params) {

		$user = elgg_extract('user', $params);
		$entity = elgg_extract('entity', $params);

		if ($entity instanceof Campaign) {
			if (!$entity->write_access_id) {
				return;
			}

			$managers = (array) get_members_of_access_collection($entity->write_access_id, true);
			if (in_array($user->guid, $managers)) {
				return true;
			}
		}

		if ($entity instanceof Reward) {
			$container = $entity->getContainerEntity();
			if ($container && $container->started && !elgg_is_admin_logged_in()) {
				return false;
			}
		}

		if ($entity instanceof Transaction && !$entity instanceof Commitment) {
			$merchant = $entity->getMerchant();
			if ($merchant instanceof Campaign) {
				// Campaign contributions are received by the site,
				// they are only refundable by the site admin
				return elgg_is_admin_logged_in();
			}
		}
	}

	/**
	 * Check delete permissions
	 *
	 * @param string $hook   "permissions_check:delete"
	 * @param string $type   "object"
	 * @param bool   $return Permission
	 * @param array  $params Hook params
	 * @return bool
	 */
	public static function canDelete($hook, $type, $return, $params) {

		$user = elgg_extract('user', $params);
		$entity = elgg_extract('entity', $params);

		if ($entity instanceof Campaign) {
			if ($entity->started && !elgg_is_admin_logged_in()) {
				return false;
			}
		}

		if ($entity instanceof Reward) {
			$container = $entity->getContainerEntity();
			if ($container && $container->started && !elgg_is_admin_logged_in()) {
				return false;
			}
		}
	}

	/**
	 * Adds "Managers" collections that user is a member of to the user's write access array
	 *
	 * @param string $hook   "access:collections:write"
	 * @param string $type   "user"
	 * @param bool   $return Collections
	 * @param array  $params Hook params
	 * @return bool
	 */
	public static function setupWriteAccess($hook, $type, $return, $params) {

		if (!elgg_in_context('campaigns')) {
			return;
		}

		$user_id = elgg_extract('user_id', $params);
		if (!$user_id) {
			return;
		}

		$campaign = elgg_get_config('current_campaign');
		if ($campaign instanceof Campaign) {
			$campaign_where = "AND ac.owner_guid = $campaign->guid";
		} else if (isset($campaign)) {
			return;
		}

		$dbprefix = elgg_get_config('dbprefix');
		$query = "
			SELECT ac.id as id, oe.title as title FROM {$dbprefix}access_collections ac
			JOIN {$dbprefix}access_collection_membership acm ON ac.id = acm.access_collection_id
			JOIN {$dbprefix}objects_entity oe ON ac.owner_guid = oe.guid 
			WHERE ac.name = 'managers' AND acm.user_guid = $user_id
			$campaign_where
		";

		$acls = get_data($query);

		if ($acls) {
			foreach ($acls as $acl) {
				$return["$acl->id"] = elgg_echo('campaigns:acl:managers', [$acl->title]);
			}
		}

		foreach ($return as $key => $label) {
			if ($label !== 'managers') {
				continue;
			}
			$acl = get_access_collection($key);
			$owner = get_entity($acl->owner_guid);
			if (!$owner) {
				continue;
			}
			$return[$key] = elgg_echo('campaigns:acl:managers', [$owner->getDisplayName()]);
		}

		return $return;
	}

	/**
	 * Sync access of contained entities when campaign access changes
	 *
	 * @param string     $event  "update"
	 * @param string     $type   "object"
	 * @param ElggEntity $entity Entity
	 * @return void
	 */
	public static function syncAccess($event, $type, $entity) {
		if (!$entity instanceof Campaign) {
			return;
		}

		$ia = elgg_set_ignore_access(true);
		$children = new ElggBatch('elgg_get_entities', [
			'type' => 'object',
			'subtype' => [
				Donation::SUBTYPE,
				Donor::SUBTYPE,
				Reward::SUBTYPE,
				NewsItem::SUBTYPE,
				ReliefItem::SUBTYPE,
				Commitment::SUBTYPE,
			],
			'container_guid' => $entity->guid,
			'wheres' => array(
				"e.access_id != {$entity->access_id}"
			),
			'limit' => 0,
		]);

		foreach ($children as $child) {
			$child->access_id = $entity->access_id;
			$child->save();
		}

		elgg_set_ignore_access($ia);
	}

}
