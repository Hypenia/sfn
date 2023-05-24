<?php

use hypeJunction\Payments\SessionStorage;
use SBW\Campaigns\Campaign;

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof Campaign) {
	return;
}

$storage = new SessionStorage();
$order = $storage->get($entity->guid);

if (!$order) {
	forward("campaigns/give/$entity->guid");
}

$items = $order->all();
if (empty($items)) {
	forward("campaigns/give/$entity->guid");
	return;
}

echo elgg_view('payments/order', [
	'order' => $order,
]);

if ($entity->model == Campaign::MODEL_RELIEF) {
	$submit_label = elgg_echo('campaigns:checkout:commit');
} else {
	$submit_label = elgg_echo('campaigns:checkout:pay');
}

$user = elgg_get_logged_in_user_entity();

echo elgg_view_field([
	'#type' => 'fieldset',
	'legend' => elgg_echo('campaigns:checkout:donor'),
	'align' => 'horizontal',
	'fields' => [
			[
			'#type' => 'email',
			'#label' => elgg_echo('campaigns:checkout:email'),
			'name' => 'email',
			'required' => true,
			'value' => elgg_extract('email', $vars, $user->email),
		],
			[
			'#type' => 'text',
			'#label' => elgg_echo('campaigns:checkout:first_name'),
			'name' => 'first_name',
			'required' => true,
			'value' => elgg_extract('first_name', $vars, $user->first_name),
		],
			[
			'#type' => 'text',
			'#label' => elgg_echo('campaigns:checkout:last_name'),
			'name' => 'last_name',
			'required' => true,
			'value' => elgg_extract('last_name', $vars, $user->last_name),
		],
			[
			'#type' => 'text',
			'#label' => elgg_echo('campaigns:checkout:phone'),
			'name' => 'phone',
			'required' => true,
			'value' => elgg_extract('phone', $vars, $user->campaigns_phone),
		],
			[
			'#type' => 'text',
			'#label' => elgg_echo('campaigns:checkout:company_name'),
			'name' => 'company_name',
			'value' => elgg_extract('company_name', $vars, $user->campaigns_company_name),
		],
			[
			'#type' => 'text',
			'#label' => elgg_echo('campaigns:checkout:tax_id'),
			'name' => 'tax_id',
			'value' => elgg_extract('tax_id', $vars, $user->campaigns_tax_id),
		],
	]
]);

$contact = elgg_extract('contact', $vars);
echo elgg_view_field([
	'#type' => 'fieldset',
	'legend' => elgg_echo('campaigns:postal_address'),
	'#class' => 'campaigns-postal-address',
	'align' => 'horizontal',
	'data-address' => 'shipping',
	'fields' => [
			[
			'#type' => 'text',
			'#label' => elgg_echo('campaigns:postal_address:street_address'),
			'name' => "contact[street_address]",
			'value' => elgg_extract('street_address', $contact, $user->campaigns_street_address),
			'required' => true,
			'data-part' => 'street_address',
		],
			[
			'#type' => 'text',
			'#label' => elgg_echo('campaigns:postal_address:extended_address'),
			'name' => "contact[extended_address]",
			'value' => elgg_extract('extended_address', $contact, $user->campaigns_extended_address),
			'data-part' => 'extended_address',
		],
			[
			'#type' => 'text',
			'#label' => elgg_echo('campaigns:postal_address:locality'),
			'name' => "contact[locality]",
			'value' => elgg_extract('locality', $contact, $user->campaigns_locality),
			'required' => true,
			'data-part' => 'locality',
		],
			[
			'#type' => 'text',
			'#label' => elgg_echo('campaigns:postal_address:region'),
			'name' => "contact[region]",
			'value' => elgg_extract('region', $contact, $user->campaigns_region),
			'required' => true,
			'data-part' => 'region',
		],
			[
			'#type' => 'text',
			'#label' => elgg_echo('campaigns:postal_address:postal_code'),
			'name' => "contact[postal_code]",
			'value' => elgg_extract('postal_code', $contact, $user->campaigns_postal_code),
			'required' => true,
			'data-part' => 'postal_code',
		],
			[
			'#type' => 'country',
			'#label' => elgg_echo('campaigns:postal_address:country'),
			'name' => "contact[country_code]",
			'value' => elgg_extract('country_code', $contact, $user->campaigns_country_code),
			'required' => true,
			'data-part' => 'country_code',
		],
	]
]);

$payment_method = $order->payment_method;

// An extension point for payment providers
$params = $vars;
$params['order'] = $order;
$params['#type'] = "campaigns/payment/$payment_method";
echo elgg_view_field($params);

echo elgg_view_field([
	'#type' => 'checkbox',
	'label' => elgg_echo('campaigns:checkout:anonymize'),
	'name' => 'anonymize',
]);

if (!elgg_is_logged_in()) {
	echo elgg_view_field([
		'#type' => 'checkbox',
		'#class' => 'campaigns-checkbox-register',
		'label' => elgg_echo('campaigns:checkout:register'),
		'name' => 'register',
		'checked' => true,
	]);

	echo elgg_view_field([
		'#type' => 'fieldset',
		'#class' => 'campaigns-checkout-register',
		'align' => 'horizontal',
		'fields' => [
				[
				'#type' => 'text',
				'#label' => elgg_echo('username'),
				'required' => true,
				'name' => 'username',
			],
				[
				'#type' => 'password',
				'#label' => elgg_echo('password'),
				'required' => true,
				'name' => 'password',
			],
		],
	]);
}

echo elgg_view_field([
	'#type' => 'longtext',
	'#label' => elgg_echo('campaigns:checkout:comment'),
	'#help' => elgg_echo('campaigns:checkout:comment:help'),
	'name' => 'comment',
	'rows' => 2,
	'visual' => false,
]);

echo elgg_view_field([
	'#type' => 'checkbox',
	'label' => elgg_echo('campaigns:checkout:subscribe'),
	'name' => 'subscribe',
	'checked' => true,
]);

$link = elgg_view('output/url', [
	'target' => '_blank',
	'href' => 'campaigns/terms/campaign?guid=' . $entity->guid,
	'text' => elgg_echo('campaigns:terms:campaign', [$entity->getDisplayName()]),
	'class' => 'elgg-lightbox',
		]);

echo elgg_view_field([
	'#type' => 'checkbox',
	'name' => 'campaign_rules',
	'value' => '1',
	'label' => elgg_echo('campaigns:field:terms', [$link]),
	'required' => true,
]);

$terms = elgg_get_plugin_setting('terms:donor', 'sbw_campaigns');
if ($terms) {
	$link = elgg_view('output/url', [
		'target' => '_blank',
		'href' => 'campaigns/terms/donor',
		'text' => elgg_echo('campaigns:terms:donor'),
		'class' => 'elgg-lightbox',
	]);
	echo elgg_view_field([
		'#type' => 'checkbox',
		'name' => 'donor_terms',
		'value' => '1',
		'label' => elgg_echo('campaigns:field:terms', [$link]),
		'required' => true,
	]);
}

echo elgg_view('input/hidden', [
	'name' => 'guid',
	'value' => $entity->guid,
]);

$footer = elgg_view_field([
	'#type' => 'fieldset',
	'align' => 'horizontal',
	'fields' => [
			[
			'#type' => 'submit',
			'value' => $submit_label,
		],
			[
			'#type' => 'campaigns/cancel',
			'entity' => $entity,
		]
	]
		]);

elgg_set_form_footer($footer);
?>
<script>
	require(['forms/campaigns/checkout']);
</script>
