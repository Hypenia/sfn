<?php

$entity = elgg_extract('entity', $vars);

echo elgg_view_field([
	'#type' => 'plaintext',
	'#label' => elgg_echo('payments:wire:instructions'),
	'#help' => elgg_echo('payments:wire:instructions:help'),
	'name' => 'params[wire_instructions]',
	'value' => $entity->wire_instructions,
]);

