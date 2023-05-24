<?php

namespace SBW\Campaigns;

use ElggObject;
use hypeJunction\Payments\SerializedMetadata;

class NewsItem extends ElggObject {

	use SerializedMetadata;
	
	const SUBTYPE = 'campaign_news';

	/**
	 * {@inheritdoc}
	 */
	protected function initializeAttributes() {
		parent::initializeAttributes();
		$this->attributes['subtype'] = self::SUBTYPE;
	}

}
