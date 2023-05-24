<?php

use hypeJunction\Payments\Transaction;

$invoice = get_input('invoice');
$status = get_input('status');

$options = [
	'types' => 'object',
	'subtypes' => Transaction::SUBTYPE,
	'list_type' => 'table',
	'metadata_name_value_pairs' => [
			[
			'name' => 'payment_method',
			'value' => 'wire',
		],
	],
	'columns' => [
		elgg()->table_columns->transaction_id(),
		elgg()->table_columns->time_created(null, [
			'format' => 'M j, Y H:i',
		]),
		elgg()->table_columns->payment_method(),
		elgg()->table_columns->customer(),
		elgg()->table_columns->merchant(),
		elgg()->table_columns->amount(),
		elgg()->table_columns->payment_status(),
	],
	'item_class' => 'payments-transaction',
	'no_results' => elgg_echo('payments:transactions:no_results'),
];

if ($invoice) {
	$options['guids'] = $invoice;
}

if ($status) {
	$options['metadata_name_value_pairs'][] = [
		'name' => 'status',
		'value' => $status,
	];
}

echo elgg_view_form('payments/wire/search', [
	'disable_security' => true,
	'method' => 'GET',
	'action' => 'admin/payments/payments_wire',
]);

echo elgg_list_entities_from_metadata($options);
