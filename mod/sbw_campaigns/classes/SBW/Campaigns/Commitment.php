<?php

namespace SBW\Campaigns;

use hypeJunction\Payments\Transaction;

class Commitment extends Transaction {

	const SUBTYPE = 'campaign_commitment';

	const STATUS_COMMITTED = 'committed';
	const STATUS_CONFIRMED = 'confirmed';
	const STATUS_RECEIVED = 'received';
	
	/**
	 * {@inheritdoc}
	 */
	protected function initializeAttributes() {
		parent::initializeAttributes();
		$this->attributes['subtype'] = self::SUBTYPE;
	}

}
