<?php

namespace SBW\Campaigns;

use hypeJunction\Payments\Merchant;
use SBW\Campaigns\Donation;
use SBW\Campaigns\Reward;

/**
 * @property string $model
 * @property int    $calendar_start
 * @property int    $calendar_end
 * @property int    $target_amount
 * @property int    $donation_minimum
 * @property string $currency
 * @property string $website
 * @property string $video_url
 * @property int   $started
 * @property int    $ended
 * @property int    $published
 * @property int    $verified
 * @property string $rules
 * @property int    ${'terms:campaigner'}
 * @property int    $net_amount
 * @property int    $gross_amount
 * @property int    $avg_donation_amount
 * @property float  $funded_percentage
 * @property int    $processor_fees
 * @property int    $backers
 * @property string $payout_instructions
 */
class Campaign extends Merchant {

	const SUBTYPE = 'campaign';
	const BASE_CURRENCY = 'EUR';
	const MODEL_ALL_OR_NOTHING = 'all_or_nothing';
	const MODEL_MONEY_POT = 'money_pot';
	const MODEL_RELIEF = 'relief';

	/**
	 * {@inheritdoc}
	 */
	protected function initializeAttributes() {
		parent::initializeAttributes();
		$this->attributes['subtype'] = self::SUBTYPE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function save() {
		if (!$this->guid) {
			parent::save();
		}
		if (!isset($this->started)) {
			$this->started = false;
		}
		if (!isset($this->ended)) {
			$this->ended = false;
		}
		if (!isset($this->published)) {
			$this->published = false;
		}
		if (!isset($this->verified)) {
			$requires_verification = elgg_get_plugin_setting('require_verification', 'sbw_campaigns');
			$this->verified = !$requires_verification;
		}
		if (!isset($this->write_access_id)) {
			$write_acl_id = create_access_collection('managers', $this->guid);
			add_user_to_access_collection($this->owner_guid, $write_acl_id);
			$this->write_access_id = $write_acl_id;
		}
		if ($this->write_access_id && (!$this->verified || !$this->published)) {
			// Only allow managers to see entities that are draft or unpublished
			if (!isset($this->future_access_id)) {
				$this->future_access_id = $this->access_id;
			}
			$this->access_id = $this->write_access_id;
		}
		if ($this->isVerified() && $this->isPublished()) {
			$this->access_id = $this->future_access_id ? $this->future_access_id : ACCESS_PUBLIC;
			if ($this->calendar_start <= time() && !$this->started) {
				$this->start();
			}
		}

		if (!isset($this->currency)) {
			$this->currency = self::BASE_CURRENCY;
		}

		return parent::save();
	}

	/**
	 * Check if campaign has started
	 * @return boolean
	 */
	public function isActive() {
		return ($this->started && !$this->ended);
	}

	/**
	 * Start a campaign
	 * @return bool
	 */
	public function start() {
		if ($this->started) {
			return false;
		}
		
		if (!elgg_trigger_event('start', 'object', $this)) {
			return false;
		}

		$this->started = time();

		if (!$this->save()) {
			return false;
		}

		$river = elgg_get_river([
			'object_guids' => $this->guid,
			'action_types' => ['publish', 'start'],
			'limit' => 0,
		]);

		if ($river) {
			foreach ($river as $river_item) {
				$river_item->delete();
			}
		}

		elgg_create_river_item([
			'view' => 'river/object/campaign/start',
			'action_type' => 'start',
			'subject_guid' => $this->owner_guid,
			'object_guid' => $this->guid,
			'target_guid' => $this->container_guid,
		]);

		return true;
	}

	/**
	 * End a campaign
	 * @return bool
	 */
	public function end() {

		if ($this->ended) {
			return false;
		}
		
		if (!elgg_trigger_event('end', 'object', $this)) {
			return false;
		}

		$this->ended = time();

		if (!$this->save()) {
			return false;
		}

		elgg_create_river_item([
			'view' => 'river/object/campaign/end',
			'action_type' => 'end',
			'subject_guid' => $this->owner_guid,
			'object_guid' => $this->guid,
			'target_guid' => $this->container_guid,
		]);
		
		return true;
	}

	/**
	 * Published or draft
	 * @return boolean
	 */
	public function isPublished() {
		return ($this->published);
	}

	/**
	 * Sets published status
	 * @return boolean
	 */
	public function publish() {
//		$rewards = $this->getRewards(['count' => true]);
//		if (!$rewards) {
//			return false;
//		}

		if (!elgg_trigger_event('publish', 'object', $this)) {
			return false;
		}

		$this->published = time();


		if (!$this->save()) {
			return false;
		}

// Temporarily disabling these river entries, because they are not deleted as
// expected when the campaign starts due to
// https://github.com/Elgg/Elgg/issues/10653
// 
//		$river = elgg_get_river([
//			'object_guids' => $this->guid,
//			'action_types' => 'publish',
//			'limit' => 0,
//		]);
//
//		if ($river) {
//			foreach ($river as $river_item) {
//				$river_item->delete();
//			}
//		}
//
//		elgg_create_river_item([
//			'view' => 'river/object/campaign/publish',
//			'action_type' => 'publish',
//			'subject_guid' => $this->owner_guid,
//			'object_guid' => $this->guid,
//			'target_guid' => $this->container_guid,
//		]);

		if (!$this->isVerified()) {
			$user = elgg_get_logged_in_user_entity();
			$admins = elgg_get_admins();
			$admin_guids = [];
			foreach ($admins as $admin) {
				$admin_guids[] = $admin->guid;
			}
			$subject = elgg_echo('campaigns:publish:notify:subject');
			$message = elgg_echo('campaigns:publish:notify:body', [
				$user->getDisplayName(),
				$this->getDisplayName(),
				$this->getURL(),
			]);
			notify_user($admin_guids, $user->guid, $subject, $message, [
				'action' => 'publish',
				'object' => $this,
			]);
		}

		return true;
	}

	/**
	 * Returns verification status
	 * @return boolean
	 */
	public function isVerified() {
		return ($this->verified);
	}

	/**
	 * Sets verified status
	 * @return boolean
	 */
	public function verify() {
		if (!elgg_trigger_event('verify', 'object', $this)) {
			return false;
		}

		$this->verified = time();
		if (!$this->save()) {
			return false;
		}

		return true;
	}

	/**
	 * Returns an array of options to retrieve campaign rewards
	 * 
	 * @param array $options      Additional options
	 * @param array $get_entities If false, will return options
	 * @return Reward[]|array|false
	 */
	public function getRewards(array $options = [], $get_entities = true) {

		$defaults = [
			'types' => 'object',
			'subtypes' => Reward::SUBTYPE,
			'container_guids' => $this->guid,
			'limit' => 0,
			'order_by_metadata' => [
				'name' => 'donation_minimum',
				'direction' => 'asc',
				'as' => 'integer',
			],
		];

		$options = array_merge($defaults, $options);
		if (!$get_entities) {
			return $options;
		}

		return elgg_get_entities_from_metadata($options);
	}

	/**
	 * Add a donation to a campaign
	 *
	 * @param Donation $donation Donation object
	 * @return void
	 */
	public function addDonation(Donation $donation) {

		$user = get_user_by_email($donation->email);
		if (!$donation->anonymous && $user) {
			elgg_create_river_item([
				'view' => 'river/object/campaign/give',
				'action_type' => 'give',
				'subject_guid' => $user[0]->guid,
				'object_guid' => $donation->guid,
				'target_guid' => $this->container_guid,
			]);
		} else {
			elgg_create_river_item([
				'view' => 'river/object/campaign/receive',
				'action_type' => 'receive',
				'subject_guid' => $this->guid,
				'object_guid' => $donation->guid,
				'target_guid' => $this->container_guid,
			]);
		}

		$funded = $this->funded_percentage;

		$this->backers++;
		$this->net_amount += $donation->net_amount;
		$this->gross_amount += $donation->gross_amount;
		$this->funded_percentage = ($this->net_amount / $this->target_amount) * 100;

		$milestones = [25, 50, 75, 100];
		foreach ($milestones as $milestone) {
			if ($this->milestone >= $milestone) {
				continue;
			}
			if ($funded < $milestone && $this->funded_percentage >= $milestone) {
				$this->milestone = $milestone;
				elgg_trigger_event('milestone', 'object', $this);

				elgg_create_river_item([
					'view' => 'river/object/campaign/milestone',
					'action_type' => 'milestone',
					'subject_guid' => $this->owner_guid,
					'object_guid' => $this->guid,
					'target_guid' => $this->container_guid,
				]);

				break;
			}
		}
	}

	/**
	 * Remove a failed/denied donation from campaign
	 *
	 * @param Donation $donation Donation object
	 * @return void
	 */
	public function removeDonation(Donation $donation) {
		$this->backers--;
		$this->net_amount -= $donation->net_amount;
		$this->gross_amount -= $donation->gross_amount;
		$this->funded_percentage = ($this->net_amount / $this->target_amount) * 100;
	}

	/**
	 * Returns campaign managers
	 * @return \ElggUser[]
	 */
	public function getManagers() {
		return get_members_of_access_collection($this->write_access_id) ?: [$this->getOwnerEntity()];
	}

}
