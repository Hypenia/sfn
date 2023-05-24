<?php

if (elgg_is_sticky_form('campaigns/give')) {
	$values = elgg_get_sticky_values('campaigns/give');
	elgg_clear_sticky_form('campaigns/give');
	$vars = array_merge($vars, $values);
}

echo elgg_view_form('campaigns/give', [], $vars);