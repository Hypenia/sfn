<?php

$full = elgg_extract('full_view', $vars, false);

if ($full) {
	echo elgg_view('object/campaign/full', $vars);
} else if (elgg_extract('list_type', $vars) == 'gallery') {
	echo elgg_view('object/campaign/gallery', $vars);
} else {
	echo elgg_view('object/campaign/summary', $vars);
}