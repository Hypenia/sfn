<?php

use hypeJunction\Payments\Amount;
use hypeJunction\Payments\Transaction;
use hypeJunction\Payments\TransactionInterface;
use SBW\Campaigns\Campaign;
use SBW\Campaigns\Commission;
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

$transactions = new ElggBatch('elgg_get_entities_from_relationship', [
	'types' => 'object',
	'subtypes' => Transaction::SUBTYPE,
	'relationship' => 'merchant',
	'relationship_guid' => (int) $campaign->guid,
	'inverse_relationship' => false,
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

/**
 * @return Amount
 */
$processor_fee = function(TransactionInterface $transaction) {
	if ($transaction->getStatus() == TransactionInterface::STATUS_PAID) {
		return $transaction->getProcessorFee();
	}
	return new Amount(0, $transaction->currency);
};

/**
 * @return Amount
 */
$site_commission = function(TransactionInterface $transaction) use ($processor_fee) {
	if ($transaction->getStatus() == TransactionInterface::STATUS_PAID) {
		$campaign = $transaction->getContainerEntity();
		if ($campaign->model == Campaign::MODEL_ALL_OR_NOTHING) {
			$commission_rate = (float) elgg_get_plugin_setting('all_or_nothing_fee', 'sbw_campaigns', 0);
		} else if ($campaign->model == Campaign::MODEL_MONEY_POT) {
			$commission_rate = (float) elgg_get_plugin_setting('money_pot_fee', 'sbw_campaigns', 0);
		} else {
			$commission_rate = 0;
		}

		$gross = $transaction->getAmount();

		$commission = new Commission('site_commission', $commission_rate);
		$commission->setBaseAmount(new Amount($gross->getAmount(), $transaction->currency));

		return $commission->getTotalAmount();
	}

	return new Amount(0, $transaction->currency);
};

$headers = [
	'date' => function(TransactionInterface $transaction) {
		return date('j M, Y H:i', $transaction->time_created);
	},
	'campaign' => function(TransactionInterface $transaction) {
		return $transaction->getContainerEntity()->getDisplayName();
	},
	'merchant' => function(TransactionInterface $transaction) {
		$merchant = $transaction->getMerchant();
		if ($merchant instanceof ElggEntity) {
			$owner = $merchant->getOwnerEntity();
			if ($owner) {
				return "$owner->name [$owner->email]";
			}
		}
		return $merchant ? $merchant->title : '';
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
	'gross_amount' => function(TransactionInterface $transaction) {
		return $transaction->getAmount()->getConvertedAmount();
	},
	'processor_fee' => function(TransactionInterface $transaction) use ($processor_fee) {
		return $processor_fee($transaction)->getConvertedAmount();
	},
	'site_commission' => function(TransactionInterface $transaction) use ($site_commission) {
		return $site_commission($transaction)->getConvertedAmount();
	},
	'net_amount' => function(TransactionInterface $transaction) use ($processor_fee, $site_commission) {
		$net = $transaction->getAmount();
		$fee = $processor_fee($transaction);
		$commission = $site_commission($transaction);

		$gross = $net->getAmount() - $fee->getAmount() - $commission->getAmount();
		return (new Amount($gross, $transaction->currency))->getConvertedAmount();
	},
	'payee' => function(TransactionInterface $transaction) {
		$customer = $transaction->getCustomer();
		return $customer ? $customer->name : '';
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

$fh = @fopen('php://output', 'w');
$headerDisplayed = false;

foreach ($transactions as $transaction) {
	/* @var $transaction TransactionInterface */

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

