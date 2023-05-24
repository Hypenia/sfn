<?php

$fields = elgg_trigger_plugin_hook('fields', 'campaigns/edit/relief_item', $vars, []);

foreach ($fields as $field) {
	echo elgg_view_field($field);
}

$footer = elgg_view_field([
	'#type' => 'submit',
	'value' => elgg_echo('save'),
]);

elgg_set_form_footer($footer);