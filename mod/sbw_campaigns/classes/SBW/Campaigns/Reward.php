<?php

namespace SBW\Campaigns;

use hypeJunction\Payments\Product;

/**
 * @property int $donation_minimum
 * @property int $price
 * @property int $quantity
 */
class Reward extends Product {

	const SUBTYPE = 'campaign_reward';

	/**
	 * {@inheritdoc}
	 */
	protected function initializeAttributes() {
		parent::initializeAttributes();
		$this->attributes['subtype'] = self::SUBTYPE;
	}

}
