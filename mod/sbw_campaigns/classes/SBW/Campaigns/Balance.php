<?php

namespace SBW\Campaigns;

use hypeJunction\Payments\Transaction;

class Balance extends Transaction {

	const SUBTYPE = 'campaign_balance';

	/**
	 * {@inheritdoc}
	 */
	protected function initializeAttributes() {
		parent::initializeAttributes();
		$this->attributes['subtype'] = self::SUBTYPE;
	}

}
