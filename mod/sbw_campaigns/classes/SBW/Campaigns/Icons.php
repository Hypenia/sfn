<?php

namespace SBW\Campaigns;

class Icons {
	
	/**
	 * Configure icon sizes
	 * 
	 * @param string $hook   "entity:icon:sizes"
	 * @param string $type   "object"
	 * @param array  $return Sizes
	 * @param array  $params Hook params
	 * @return array
	 */
	public static function configureIconSizes($hook, $type, $return, $params) {

		$subtype = elgg_extract('entity_subtype', $params);

		if (!in_array($subtype, [Campaign::SUBTYPE, Reward::SUBTYPE, ReliefItem::SUBTYPE])) {
			return;
		}

		$return['card'] = [
			'w' => 320,
			'h' => 180,
			'square' => false,
			'upscale' => true,
		];

		$return['large']['square'] = true;
		
		return $return;
	}
}
