<?php

namespace SBW\Campaigns;

use hypeJunction\Payments\Product;
use LogicException;

/**
 * Arbitrary amount contribution
 */
class Contribution extends Product {

	const SUBTYPE = 'campaign_contribution';

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
	public function inStock($quantity = 1) {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayName() {
		if (isset($this->title)) {
			return $this->title;
		}
		$container = $this->getContainerEntity();
		if (!$container) {
			return elgg_echo('campaigns:contribution');
		} else {
			return elgg_echo('campaigns:contribution:target', [$container->getDisplayName()]);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTitle() {
		return $this->getDisplayName();
	}

	/**
	 * {@inheritdoc}
	 */
	public function save() {
		throw new LogicException(__CLASS__ . ' instances can not be saved');
	}

}
