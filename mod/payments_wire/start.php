<?php

/**
 * Wire Payments
 *
 * @author Ismayil Khayredinov <info@hypejunction.com>
 * @copyright Copyright (c) 2016, Ismayil Khayredinov
 * @copyright Copyright (c) 2016, Social Business World
 */
require_once __DIR__ . '/autoloader.php';

use hypeJunction\Payments\Wire\Payments;
use hypeJunction\Payments\Wire\Router;

elgg_register_event_handler('init', 'system', function() {

	elgg_register_plugin_hook_handler('route', 'payments', [Router::class, 'controller'], 100);

	elgg_register_plugin_hook_handler('refund', 'payments', [Payments::class, 'refundTransaction']);
	
	elgg_register_action('payments/checkout/wire', __DIR__ . '/actions/payments/checkout/wire.php', 'public');

	elgg_register_admin_menu_item('administer', 'payments_wire', 'payments');
});

