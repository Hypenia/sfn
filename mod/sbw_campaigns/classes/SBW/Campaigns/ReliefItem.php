<?php

namespace SBW\Campaigns;

use ElggEntity;
use hypeJunction\Payments\Product;

/**
 * @property int $required_quantity
 */
class ReliefItem extends Product {

	const SUBTYPE = 'campaign_relief_item';

	/**
	 * {@inheritdoc}
	 */
	protected function initializeAttributes() {
		parent::initializeAttributes();
		$this->attributes['subtype'] = self::SUBTYPE;
	}

	public function save() {

		$campaign = $this->getContainerEntity();
		$result = parent::save();
		if ($result) {
			Commitments::updateStats($campaign);
		}
		return $result;
	}

	public function delete($recursive = true) {
		$campaign = $this->getContainerEntity();
		$result = parent::delete($recursive);
		if ($result) {
			Commitments::updateStats($campaign);
		}
		return $result;
	}

	public function addCommitment(ElggEntity $donor, $quantity) {
		$this->annotate('committed', $quantity, ACCESS_PUBLIC, $donor->guid);
	}

	public function getCommitments() {
		return $this->getAnnotationsSum('committed');
	}

	public function addDelivery(ElggEntity $donor, $quantity) {
		$this->annotate('delivered', $quantity, ACCESS_PUBLIC, $donor->guid);
	}

	public function getDeliveries() {
		return $this->getAnnotationsSum('delivered');
	}

}
