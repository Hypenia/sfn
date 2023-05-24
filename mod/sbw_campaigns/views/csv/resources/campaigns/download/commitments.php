<?php

use hypeJunction\Payments\TransactionInterface;
use SBW\Campaigns\Campaign;
use SBW\Campaigns\Commitment;
use SBW\Campaigns\ReliefItem;
use SBW\Campaigns\Reward;

$guid = elgg_extract('guid', $vars);
$campaign = get_entity($guid);
if (!$campaign instanceof Campaign || !$campaign->canEdit()) {
	forward('', '404');
}

$ts = time();
$filename = "transactions-{$campaign->guid}-{$ts}.csv";

elgg_set_http_header("Content-type: text/csv");
elgg_set_http_header("Content-Disposition: attachment; filename={$filename}");

$transactions = new ElggBatch('elgg_get_entities', [
	'types' => 'object',
	'subtypes' => Commitment::SUBTYPE,
	'container_guids' => (int) $campaign->guid,
	'limit' => 0,
		]);

$address_part = function(TransactionInterface $transaction, $part) {
	$order = $transaction->getOrder();
	if (!$order) {
		return 'ERROR';
	}
	$shipping = $order->getShippingAddress();
	if (!$shipping) {
		return '';
	}
	return $shipping->$part ?: '';
};

$billing_part = function(TransactionInterface $transaction, $part) {
	$order = $transaction->getOrder();
	if (!$order) {
		return 'ERROR';
	}
	$billing = $order->getBillingAddress();
	if (!$billing) {
		return '';
	}
	$part = substr($part, 8); // remove billing_ prefix
	return $billing->$part ?: '';
};

$transaction_meta = function(TransactionInterface $transaction, $name) {
	return $transaction->$name ?: '';
};

$headers = [
	'date' => function(TransactionInterface $transaction) {
		return date('j M, Y H:i', $transaction->time_created);
	},
	'invoice' => function(TransactionInterface $transaction) {
		return $transaction->guid;
	},
	'id' => function(TransactionInterface $transaction) {
		return $transaction->getId();
	},
	'method' => function(TransactionInterface $transaction) {
		return elgg_echo("payments:method:{$transaction->getPaymentMethod()}");
	},
	'status' => function(TransactionInterface $transaction) {
		return elgg_echo("payments:status:{$transaction->getStatus()}");
	},
	'anonymous' => function(TransactionInterface $transaction) {
		return $transaction->anonymous ? 'yes' : 'no';
	},
	'currency' => function(TransactionInterface $transaction) {
		return $transaction->getAmount()->getCurrency();
	},
	'amount' => function(TransactionInterface $transaction) {
		return $transaction->getAmount()->getConvertedAmount();
	},
	'payee' => function(TransactionInterface $transaction) {
		$customer = $transaction->getCustomer();
		return $customer ? $customer->name : '';
	},
	'merchant' => function(TransactionInterface $transaction) {
		$merchant = $transaction->getMerchant();
		return $merchant ? $merchant->title : '';
	},
	'reward' => function(TransactionInterface $transaction) {
		$order = $transaction->getOrder();
		if (!$order) {
			return 'ERROR';
		}

		$items = $order->all();
		foreach ($items as $item) {
			$product = $item->getProduct();
			if ($product instanceof Reward) {
				return $product->title;
			}
		}
		return '';
	},
	'first_name' => $transaction_meta,
	'last_name' => $transaction_meta,
	'email' => $transaction_meta,
	'phone' => $transaction_meta,
	'company_name' => $transaction_meta,
	'tax_id' => $transaction_meta,
	'street_address' => $address_part,
	'extended_address' => $address_part,
	'locality' => $address_part,
	'region' => $address_part,
	'postal_code' => $address_part,
	'country_code' => $address_part,
	'billing_street_address' => $billing_part,
	'billing_extended_address' => $billing_part,
	'billing_locality' => $billing_part,
	'billing_region' => $billing_part,
	'billing_postal_code' => $billing_part,
	'billing_country_code' => $billing_part,
];

$relief_items = elgg_get_entities([
	'types' => 'object',
	'subtypes' => ReliefItem::SUBTYPE,
	'container_guids' => (int) $campaign->guid,
	'limit' => 0,
	'batch' => true,
		]);

foreach ($relief_items as $relief_item) {
	$headers[$relief_item->title] = function(TransactionInterface $transaction) use ($relief_item) {
		$order = $transaction->getOrder();
		if (!$order) {
			return 0;
		}
		foreach ($order->all() as $item) {
			if ($item->getId() == $relief_item->guid) {
				return $item->getQuantity();
			}
		}
	};
}

$fh = @fopen('php://output', 'w');
$headerDisplayed = false;

foreach ($transactions as $transaction) {

	if (!$transaction->amount || !$transaction->currency) {
		error_log("Something is wrong with transaction with the guid $transaction->guid. Transaction amount information is missing or corrupted");
		continue;
	}

	if (!$headerDisplayed) {
		// Use the keys from $data as the titles
		fputcsv($fh, array_keys($headers));
		$headerDisplayed = true;
	}

	$data = [];
	foreach ($headers as $key => $callback) {
		$data[$key] = call_user_func($callback, $transaction, $key);
	}

	// Put the data into the stream
	fputcsv($fh, $data);
}

fclose($fh);

