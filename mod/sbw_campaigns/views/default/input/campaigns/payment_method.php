<?php

$methods = elgg_trigger_plugin_hook('payment_methods', 'campaigns', $vars, []);

foreach ($methods as $method) {
	$id = elgg_extract('id', $method);
	$name = elgg_extract('name', $method);
	$fee = elgg_extract('fee', $method);
	$icon = elgg_extract('icon', $method);

	$label = "$icon $name";
	if ($fee) {
		$label .= elgg_echo("campaigns:payment_method:fee", [$fee]);
	}
	
	$options[$id] = $label;
}

$vars['options'] = array_flip($options);

echo elgg_view('input/radio', $vars);
