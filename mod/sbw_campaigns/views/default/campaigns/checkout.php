<?php

if (elgg_is_sticky_form('campaigns/checkout')) {
	$values = elgg_get_sticky_values('campaigns/checkout');
	elgg_clear_sticky_form('campaigns/checkout');
	$vars = array_merge($vars, $values);
}

echo elgg_view_form('campaigns/checkout', [], $vars);