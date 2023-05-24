<?php

use hypeJunction\Payments\TransactionInterface;

echo elgg_view_field([
	'#type' => 'fieldset',
	'align' => 'horizontal',
	'fields' => [
			[
			'#type' => 'text',
			'#label' => elgg_echo('payments:wire:invoice'),
			'name' => 'invoice',
			'value' => get_input('invoice'),
		],
			[
			'#type' => 'select',
			'#label' => elgg_echo('payments:payment:status'),
			'name' => 'status',
			'value' => get_input('status'),
			'options_values' => [
				'' => '',
				TransactionInterface::STATUS_NEW => elgg_echo('payments:status:new'),
				TransactionInterface::STATUS_PAYMENT_PENDING => elgg_echo('payments:status:payment_pending'),
				TransactionInterface::STATUS_PAID => elgg_echo('payments:status:paid'),
				TransactionInterface::STATUS_REFUND_PENDING => elgg_echo('payments:status:refund_pending'),
				TransactionInterface::STATUS_REFUNDED => elgg_echo('payments:status:refunded'),
				TransactionInterface::STATUS_PARTIALLY_REFUNDED => elgg_echo('payments:status:partially_refunded'),
				TransactionInterface::STATUS_FAILED => elgg_echo('payments:status:failed'),
			],
		],
			[
			'#type' => 'submit',
			'value' => elgg_echo('search'),
		],
	]
]);
