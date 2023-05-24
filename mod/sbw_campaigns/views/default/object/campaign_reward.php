<?php

$full = elgg_extract('full_view', $vars, false);

if (elgg_extract('list_type', $vars) == 'gallery') {
	echo elgg_view('object/campaign_reward/gallery', $vars);
} else {
	echo elgg_view('object/campaign_reward/summary', $vars);
}