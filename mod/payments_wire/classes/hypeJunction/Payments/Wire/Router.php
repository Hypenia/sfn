<?php

namespace hypeJunction\Payments\Wire;

use hypeJunction\Payments\Transaction;

class Router {

	/**
	 * Route payment pages
	 *
	 * @param string $hook   "route"
	 * @param string $type   "payments"
	 * @param mixed  $return New route
	 * @param array  $params Hook params
	 * @return array
	 */
	public static function controller($hook, $type, $return, $params) {

		if (!is_array($return)) {
			return;
		}

		$segments = (array) elgg_extract('segments', $return);

		if ($segments[0] !== 'wire') {
			return;
		}

		$ia = elgg_set_ignore_access(true);
		$transaction_id = $segments[1];
		$transaction = Transaction::getFromID($transaction_id);
		
		if ($transaction) {
			$output = elgg_view_resource('payments/wire', [
				'entity' => $transaction,
			]);
		}
		
		elgg_set_ignore_access($ia);

		if ($output) {
			echo $output;
			return false;
		}
	}

	/**
	 * Add IPN processor to public pages
	 *
	 * @param string $hook   "public_pages"
	 * @param string $type   "walled_garden"
	 * @param array  $return Public pages
	 * @param array  $params Hook params
	 * @return array
	 */
	public static function setPublicPages($hook, $type, $return, $params) {
		$return[] = 'payments/paypal/api';
		$return[] = 'payments/paypal/api/.*';
		return $return;
	}

}
