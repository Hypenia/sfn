<?php

namespace hypeJunction\Discovery;

class Analytics {

	/**
	 * Store temp user hash
	 *
	 * @param string $event		Equals 'login'
	 * @param string $type		Equals 'user'
	 * @param ElggUser $user
	 */
	public static function saveTempUserHash($event, $type, $user) {

		if (isset($_SESSION['discovery_hash'])) {
			create_metadata($user->guid, 'discovery_temp_hash', $_SESSION['discovery_hash'], '', $user->guid, ACCESS_PUBLIC);
			unset($_SESSION['discovery_hash']);
		}

		return true;
	}

}
