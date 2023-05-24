<?php

use SBW\Campaigns\Commitment;

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof Commitment || !$entity->canEdit()) {
	return;
}

echo elgg_view_form('campaigns/commitment/change_status', [], $vars);

