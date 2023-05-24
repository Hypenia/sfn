<?php

namespace SBW\Campaigns;

use ElggObject;
use hypeJunction\Payments\Amount;
use hypeJunction\Payments\SerializedMetadata;

/**
 * @property int    $net_amount
 * @property int    $gross_amount
 * @property string $currency
 * @property bool   $anonymous
 */
class Donation extends ElggObject {

	use SerializedMetadata;
	
	const SUBTYPE = 'campaign_donation';

	/**
	 * {@inheritdoc}
	 */
	protected function initializeAttributes() {
		parent::initializeAttributes();
		$this->attributes['subtype'] = self::SUBTYPE;
	}

	/**
	 * Get donation by transaction id
	 * 
	 * @param string $transaction_id Transaction id
	 * @return Donation|false
	 */
	public static function getFromTransactionId($transaction_id) {

		$donations = elgg_get_entities_from_metadata([
			'types' => 'object',
			'subtypes' => Donation::SUBTYPE,
			'metadata_name_value_pairs' => [
				'name' => 'transaction_id',
				'value' => $transaction_id,
			],
			'limit' => 1,
		]);

		return $donations ? $donations[0] : false;
	}

	/**
	 * Returns net amount
	 * @return Amount
	 */
	public function getNetAmount() {
		return new Amount((int) $this->net_amount, $this->currency);
	}

	/**
	 * Returns gross amount
	 * @return Amount
	 */
	public function getGrossAmount() {
		return new Amount((int) $this->gross_amount, $this->currency);
	}

}
