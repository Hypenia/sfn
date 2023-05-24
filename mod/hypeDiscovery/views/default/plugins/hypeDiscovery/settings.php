<?php

namespace hypeJunction\Discovery;

$entity = elgg_extract('entity', $vars);

echo elgg_view_input('select', array(
	'name' => 'params[bypass_access]',
	'value' => $entity->bypass_access,
	'options_values' => array(
		false => elgg_echo('option:no'),
		true => elgg_echo('option:yes')
	),
	'label' => elgg_echo('discovery:settings:bypass_access'),
	'help' => elgg_echo('discovery:settings:bypass_access:help'),
));

$registered_entities = get_registered_entity_types();
foreach ($registered_entities as $type => $subtypes) {
	if (empty($subtypes)) {
		$str = elgg_echo("item:$type");
		$chbx_options[$str] = "$type::default";
	} else {
		foreach ($subtypes as $subtype) {
			$str = elgg_echo("item:$type:$subtype");
			$chbx_options[$str] = "$type::$subtype";
		}
	}
}

echo elgg_view_input('checkboxes', array(
	'name' => 'params[discovery_type_subtype_pairs]',
	'value' => get_discoverable_type_subtype_pairs(),
	'options' => $chbx_options,
	'label' => elgg_echo('discovery:settings:discovery_type_subtype_pairs'),
	'help' => elgg_echo('discovery:settings:discovery_type_subtype_pairs:help'),
));

echo elgg_view_input('checkboxes', array(
	'name' => 'params[embed_type_subtype_pairs]',
	'value' => get_embeddable_type_subtype_pairs(),
	'options' => $chbx_options,
	'label' => elgg_echo('discovery:settings:embed_type_subtype_pairs'),
));

echo elgg_view_input('checkboxes', array(
	'name' => 'params[providers]',
	'value' => get_discovery_providers(),
	'options' => [
		elgg_echo('discovery:provider:facebook') => 'facebook',
		elgg_echo('discovery:provider:twitter') => 'twitter',
		elgg_echo('discovery:provider:linkedin') => 'linkedin',
		elgg_echo('discovery:provider:pinterest') => 'pinterest',
		elgg_echo('discovery:provider:googleplus') => 'googleplus',
	],
	'label' => elgg_echo('discovery:settings:providers'),
	'help' => elgg_echo('discovery:settings:providers:help'),
));
