<?php

$full = elgg_extract('full_view', $vars, false);

if ($full) {
	echo elgg_view('object/campaign_news/full', $vars);
} else {
	echo elgg_view('object/campaign_news/summary', $vars);
}