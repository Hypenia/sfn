<?php

$item = elgg_extract('item', $vars);
if (!$item instanceof ElggAnnotation) {
	return;
}

echo elgg_view_menu('annotation', [
	'annotation' => $item,
	'class' => 'elgg-menu-hz',
]);