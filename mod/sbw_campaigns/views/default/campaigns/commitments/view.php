<?php

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \SBW\Campaigns\Commitment) {
	return;
}

$vars['full_view'] = true;
echo elgg_view_entity($entity, $vars);