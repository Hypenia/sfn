<?php

namespace SBW\Campaigns;

use ElggObject;
use hypeJunction\Payments\SerializedMetadata;

/**
 * @property int    $amount
 * @property string $currency
 */
class Donor extends ElggObject {

	use SerializedMetadata;
	
	const SUBTYPE = 'campaign_donor';

	/**
	 * {@inheritdoc}
	 */
	protected function initializeAttributes() {
		parent::initializeAttributes();
		$this->attributes['subtype'] = self::SUBTYPE;
	}

}
