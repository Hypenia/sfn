<?php

use hypeJunction\Payments\Transaction;
use hypeJunction\Payments\Wire\Adapter;

$ia = elgg_set_ignore_access(true);

$transaction_id = get_input('transaction_id');
$transaction = Transaction::getFromId($transaction_id);

$error = false;
if ($transaction) {
	$merchant = $transaction->getMerchant();
	if ($merchant->wire_instructions) {
		$wire_instructions = $merchant->wire_instructions;
	} else {
		$wire_instructions = elgg_get_plugin_setting('wire_instructions', 'payments_wire');
	}
	$wire_instructions = elgg_trigger_plugin_hook('wire_instructions', 'wire', [
		'transaction' => $transaction,
	], $wire_instructions);

	$wire_adapter = new Adapter();
	$wire_adapter->setWireInstructions($wire_instructions);
	$response = $wire_adapter->pay($transaction);
} else {
	$error = elgg_echo('payments:error:not_found');
	$status_code = ELGG_HTTP_NOT_FOUND;
	$forward_url = REFERRER;
}

elgg_set_ignore_access($ia);

if ($error) {
	return elgg_error_response($error, $forward_url, $status_code);
}

return $response;

