<?php

namespace SBW\Campaigns;

class Maps {

	public static function setCampaignMarker($hook, $type, $marker, $params) {

		$entity = elgg_extract('entity', $params);

		if (!$entity instanceof Campaign) {
			return;
		}

		$marker->color = 'darkpurple';
		$marker->icon = 'handshake-o';

		return $marker;
	}
}
