<?php

use hypeJunction\Payments\Address;
use hypeJunction\Payments\SessionStorage;
use hypeJunction\Payments\Transaction;
use SBW\Campaigns\Campaign;
use SBW\Campaigns\Commitment;
use SBW\Campaigns\Donor;

elgg_make_sticky_form('campaigns/checkout');

$guid = get_input('guid');
$campaign = get_entity($guid);

if (!$campaign instanceof Campaign) {
	$error = elgg_echo('campaigns:error:not_found');
	return elgg_error_response($error, REFERRER, ELGG_HTTP_NOT_FOUND);
}

$storage = new SessionStorage();
$order = $storage->get($campaign->guid);
if (!$order) {
	forward("campaigns/give/$campaign->guid");
}

$donor_terms = (bool) get_input('donor_terms') || !elgg_get_plugin_setting('terms:donor', 'sbw_campaigns');
$campaign_rules = (bool) get_input('campaign_rules');

if (!$donor_terms || !$campaign_rules) {
	$error = elgg_echo('campaigns:error:checkout_terms');
	return elgg_error_response($error, REFERRER, ELGG_HTTP_BAD_REQUEST);
}

$email = get_input('email');
$first_name = get_input('first_name');
$last_name = get_input('last_name');
$company_name = get_input('company_name');
$tax_id = get_input('tax_id');
$phone = get_input('phone');

if (!$first_name || !$last_name || !$phone || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
	$error = elgg_echo('campaigns:error:required');
	return elgg_error_response($error, REFERRER, ELGG_HTTP_BAD_REQUEST);
}

$name = "$first_name $last_name";

$contact = (array) get_input('contact', []);
$street_address = elgg_extract('street_address', $contact);
$extended_address = elgg_extract('extended_address', $contact);
$locality = elgg_extract('locality', $contact);
$region = elgg_extract('region', $contact);
$postal_code = elgg_extract('postal_code', $contact);
$country_code = elgg_extract('country_code', $contact);

if (!$street_address || !$locality || !$region || !$postal_code || !$country_code) {
	$error = elgg_echo('campaigns:error:required');
	return elgg_error_response($error, REFERRER, ELGG_HTTP_BAD_REQUEST);
}

$shipping_address = new Address();
$shipping_address->street_address = $street_address;
$shipping_address->extended_address = $extended_address;
$shipping_address->locality = $locality;
$shipping_address->region = $region;
$shipping_address->postal_code = $postal_code;
$shipping_address->country_code = $country_code;

$ha = access_get_show_hidden_status();
access_show_hidden_entities(true);
$ia = elgg_set_ignore_access(true);

$users = get_user_by_email($email);
if ($users) {
	$user = array_shift($users);
} else {
	$register = get_input('register');
	$username = get_input('username');
	$password = get_input('password');

	if ($register && $username && $password) {
		try {
			$user_guid = register_user($username, $password, $name, $email);
		} catch (Exception $e) {
			register_error($e->getMessage());
		}

		if ($user_guid) {
			$user = get_entity($user_guid);
			$params = array(
				'user' => $user,
				'password' => $password,
			);
			if (!elgg_trigger_plugin_hook('register', 'user', $params, true)) {
				$ia = elgg_set_ignore_access(true);
				$user->delete();
				unset($user);
				elgg_set_ignore_access($ia);
			}
		}
	}

	if (!$user) {
		$user = new Donor();
		$user->owner_guid = $campaign->guid;
		$user->container_guid = $campaign->guid;
		$user->email = $email;
		$user->name = $name;
		$user->access_id = $campaign->access_id;
		$user->save();
	}
}

$user->campaigns_street_address = $street_address;
$user->campaigns_extended_address = $extended_address;
$user->campaigns_locality = $locality;
$user->campaigns_region = $region;
$user->campaigns_postal_code = $postal_code;
$user->campaigns_country_code = $country_code;
$user->campaigns_company_name = $company_name;
$user->campaigns_tax_id = $tax_id;
$user->campaigns_phone = $phone;

if (!$user->first_name) {
	$user->first_name = $first_name;
}
if (!$user->last_name) {
	$user->last_name = $last_name;
}

$subscribe = (bool) get_input('subscribe');
if ($subscribe) {
	$methods = elgg_get_notification_methods();
	foreach ($methods as $method) {
		elgg_add_subscription($user->guid, $method, $campaign->guid);
	}
}

if (get_input('billing_as_shipping') || $campaign->model == Campaign::MODEL_RELIEF) {
	$billing_address = clone $shipping_address;
} else {
	$billing = (array) get_input('billing', []);
	if ($billing) {
		$billing_address = new Address();
		$billing_address->street_address = elgg_extract('street_address', $billing);
		$billing_address->extended_address = elgg_extract('extended_address', $billing);
		$billing_address->locality = elgg_extract('locality', $billing);
		$billing_address->region = elgg_extract('region', $billing);
		$billing_address->postal_code = elgg_extract('postal_code', $billing);
		$billing_address->country_code = elgg_extract('country_code', $billing);
	}
}

$order->setCustomer($user);
$order->setShippingAddress($shipping_address);
$order->setBillingAddress($billing_address);

if ($campaign->model == Campaign::MODEL_RELIEF) {

	$transaction = new Commitment();
	$transaction->setOrder($order);
	$transaction->origin = 'campaigns';
	$transaction->email = $email;
	$transaction->first_name = $first_name;
	$transaction->last_name = $last_name;
	$transaction->company_name = $company_name;
	$transaction->tax_id = $tax_id;
	$transaction->phone = $phone;
	$transaction->name = $name;
	$transaction->anonymous = (bool) get_input('anonymize');
	$transaction->owner_guid = $user->guid;
	$transaction->container_guid = $campaign->guid;
	$transaction->access_id = $campaign->access_id;

	$transaction->setSerializedMetadata('comment', get_input('comment'));
	
	$transaction->save();

	$transaction->setStatus(Commitment::STATUS_COMMITTED);

	elgg_clear_sticky_form('campaigns/checkout');

	$storage->invalidate($campaign->guid);

	$forward_url = "campaigns/thankyou/$campaign->guid";
	return elgg_redirect_response($forward_url);
} else {

	$payment_method = $order->payment_method;

	$transaction = new Transaction();
	$transaction->setOrder($order);
	$transaction->setPaymentMethod($payment_method);
	$transaction->origin = 'campaigns';
	$transaction->email = $email;
	$transaction->first_name = $first_name;
	$transaction->last_name = $last_name;
	$transaction->company_name = $company_name;
	$transaction->tax_id = $tax_id;
	$transaction->phone = $phone;
	$transaction->name = $name;
	$transaction->anonymous = (bool) get_input('anonymize');
	$transaction->owner_guid = $user->guid;
	$transaction->container_guid = $campaign->guid;
	$transaction->access_id = $campaign->write_access_id;

	$transaction->setSerializedMetadata('comment', get_input('comment'));

	$transaction->save();

	elgg_clear_sticky_form('campaigns/checkout');

	$storage->invalidate($campaign->guid);

	$query = $_REQUEST;
	$query['transaction_id'] = $transaction->transaction_id;
	$query['__elgg_uri'] = null;

	$forward_url = elgg_http_add_url_query_elements("action/payments/checkout/$payment_method", $query);
	$forward_url = elgg_add_action_tokens_to_url($forward_url);

	access_show_hidden_entities($ha);
	elgg_set_ignore_access($ia);

	return elgg_redirect_response($forward_url);
}
