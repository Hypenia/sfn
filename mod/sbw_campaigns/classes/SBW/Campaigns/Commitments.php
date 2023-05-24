<?php

namespace SBW\Campaigns;

use ElggUser;
use hypeJunction\Payments\OrderItem;

class Commitments {

	/**
	 * Mark commitment as committed
	 *
	 * @param string $hook   "transaction:committed"
	 * @param string $type   "payments"
	 * @param bool   $return Processing status
	 * @param array  $params Hook params
	 * @return bool
	 */
	public static function processCommitment($hook, $type, $return, $params) {

		$commitment = elgg_extract('entity', $params);
		if (!$commitment instanceof Commitment) {
			return;
		}

		$campaign = $commitment->getMerchant();
		$committer = $commitment->getCustomer();

		$item_list = [];
		$order = $commitment->getOrder();
		if ($order) {
			foreach ($order->all() as $item) {
				$item_list[] = "$item->title - {$item->getQuantity()}";
			}
		}

		$from = elgg_get_site_entity()->email;

		$confirmation_url = elgg_http_add_url_query_elements("campaigns/confirm", [
			'd' => $commitment->guid,
			'u' => $committer->guid,
			'c' => $campaign->guid,
		]);

		$subject = elgg_echo('campaigns:commitment:confirmation_url:notify:subject', [$campaign->getDisplayName()]);
		$body = elgg_echo('campaigns:commitment:confirmation_url:notify:body', [
			$campaign->getDisplayName(),
			implode(PHP_EOL, $item_list),
			elgg_http_get_signed_url($confirmation_url),
			$campaign->getURL(),
		]);

		elgg_send_email($from, $committer->email, $subject, $body, [
			'campaign' => $campaign,
			'commitment' => $commitment,
		]);

		$subject = elgg_echo('campaigns:commitment:confirmation_url:notify_manager:subject', [$campaign->getDisplayName()]);
		$body = elgg_echo('campaigns:commitment:confirmation_url:notify_manager:body', [
			$committer->name,
			$committer->email,
			$campaign->getDisplayName(),
			implode(PHP_EOL, $item_list),
			$campaign->getURL(),
		]);

		$managers = $campaign->getManagers();
		foreach ($managers as $manager) {
			elgg_send_email($from, $manager->email, $subject, $body, [
				'campaign' => $campaign,
				'commitment' => $commitment,
			]);
		}

		self::updateStats($commitment->getMerchant());
	}

	/**
	 * Mark commitment as confirmed
	 *
	 * @param string $hook   "transaction:confirmed"
	 * @param string $type   "payments"
	 * @param bool   $return Processing status
	 * @param array  $params Hook params
	 * @return bool
	 */
	public static function processConfirmation($hook, $type, $return, $params) {

		$commitment = elgg_extract('entity', $params);
		if (!$commitment instanceof Commitment) {
			return;
		}

		$order = $commitment->getOrder();

		if ($order) {
			$items = $order->all();
			foreach ($items as $item) {
				/* @var $item OrderItem */
				$relief_item = $item->getProduct();
				if ($relief_item) {
					/* @var $relief_item ReliefItem */
					$relief_item->addCommitment($commitment->getCustomer(), $item->getQuantity());
				}
			}
		}

		$campaign = $commitment->getMerchant();

		$funded = $campaign->funded_percentage;

		self::updateStats($commitment->getMerchant());

		$milestones = [25, 50, 75, 100];
		foreach ($milestones as $milestone) {
			if ($campaign->milestone >= $milestone) {
				continue;
			}
			if ($funded < $milestone && $campaign->funded_percentage >= $milestone) {
				$campaign->milestone = $milestone;
				elgg_trigger_event('milestone', 'object', $campaign);

				elgg_create_river_item([
					'view' => 'river/object/campaign/milestone',
					'action_type' => 'milestone',
					'subject_guid' => $campaign->owner_guid,
					'object_guid' => $campaign->guid,
					'target_guid' => $campaign->container_guid,
				]);

				break;
			}
		}

		$committer = $commitment->getCustomer();

		if (!$commitment->anonymous && $committer->guid && $committer instanceof ElggUser) {
			elgg_create_river_item([
				'view' => 'river/object/campaign/give',
				'action_type' => 'give',
				'subject_guid' => $committer->guid,
				'object_guid' => $campaign->guid,
				'target_guid' => $campaign->container_guid,
			]);
		}

		$item_list = [];
		if ($order) {
			foreach ($order->all() as $item) {
				$item_list[] = "$item->title - {$item->getQuantity()}";
			}
		}

		$from = elgg_get_site_entity()->email;

		$subject = elgg_echo('campaigns:commitment:notify:subject', [$campaign->getDisplayName()]);
		$body = elgg_echo('campaigns:commitment:notify:body', [
			$campaign->getDisplayName(),
			implode(PHP_EOL, $item_list),
			$campaign->relief_delivery,
			$campaign->getURL(),
		]);

		elgg_send_email($from, $committer->email, $subject, $body, [
			'campaign' => $campaign,
			'commitment' => $commitment,
		]);

		$subject = elgg_echo('campaigns:commitment:notify_manager:subject', [$campaign->getDisplayName()]);
		$body = elgg_echo('campaigns:commitment:notify_manager:body', [
			$committer->name,
			$committer->email,
			$campaign->getDisplayName(),
			implode(PHP_EOL, $item_list),
			$campaign->relief_delivery,
			$campaign->getURL(),
		]);

		$managers = $campaign->getManagers();
		foreach ($managers as $manager) {
			elgg_send_email($from, $manager->email, $subject, $body, [
				'campaign' => $campaign,
				'commitment' => $commitment,
			]);
		}
	}

	/**
	 * Mark commitment as confirmed
	 *
	 * @param string $hook   "transaction:delivered"
	 * @param string $type   "payments"
	 * @param bool   $return Processing status
	 * @param array  $params Hook params
	 * @return bool
	 */
	public static function processDelivery($hook, $type, $return, $params) {

		$commitment = elgg_extract('entity', $params);
		if (!$commitment instanceof Commitment) {
			return;
		}

		$order = $commitment->getOrder();
		if ($order) {
			$items = $order->all();

			foreach ($items as $item) {
				/* @var $item OrderItem */
				$relief_item = $item->getProduct();
				if ($relief_item) {
					/* @var $relief_item ReliefItem */
					$relief_item->addDelivery($commitment->getCustomer(), $item->getQuantity());
				}
			}
		}

		self::updateStats($commitment->getMerchant());

		$committer = $commitment->getCustomer();
		$campaign = $commitment->getMerchant();

		$item_list = [];
		if ($order) {
			foreach ($order->all() as $item) {
				$item_list[] = "$item->title - {$item->getQuantity()}";
			}
		}

		$subject = elgg_echo('campaigns:commitment:delivered:notify:subject', [$campaign->getDisplayName()]);
		$body = elgg_echo('campaigns:commitment:delivered:notify:body', [
			$campaign->getDisplayName(),
			implode(PHP_EOL, $item_list),
			$campaign->getURL(),
		]);

		elgg_send_email(elgg_get_site_entity()->email, $committer->email, $subject, $body, [
			'campaign' => $campaign,
			'commitment' => $commitment,
		]);
	}

	/**
	 * Update commitment stats
	 * 
	 * @param Campaign $campaign Campaign
	 * @return void
	 */
	public static function updateStats(Campaign $campaign) {

		if ($campaign->model == Campaign::MODEL_RELIEF) {
			$ia = elgg_set_ignore_access(true);

			$relief_items = elgg_get_entities([
				'types' => 'object',
				'subtypes' => ReliefItem::SUBTYPE,
				'container_guids' => (int) $campaign->guid,
				'limit' => 0,
				'batch' => true,
			]);

			$required = 0;
			$committed = 0;
			foreach ($relief_items as $item) {
				/* @var $item ReliefItem */
				$required += $item->required_quantity;
				$committed += (int) $item->getCommitments();
			}

			if ($required) {
				$funded_percentage = round($committed * 100 / $required);
			} else {
				$funded_percentage = 0;
			}

			$campaign->backers = elgg_get_entities([
				'types' => 'object',
				'subtypes' => Commitment::SUBTYPE,
				'container_guids' => $campaign->guid,
				'count' => true,
				'metadata_names' => 'status',
				'metadata_values' => [Commitment::STATUS_CONFIRMED, Commitment::STATUS_RECEIVED],
			]);

			$campaign->funded_percentage = $funded_percentage;
			$campaign->committed_quantity = $committed;

			elgg_set_ignore_access($ia);
		}
	}

}
