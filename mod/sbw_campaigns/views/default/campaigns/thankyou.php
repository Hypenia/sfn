<?php

$entity = elgg_extract('entity', $vars);

if ($entity->model == \SBW\Campaigns\Campaign::MODEL_RELIEF) {
	echo elgg_format_element('p', [

	], elgg_echo('campaigns:thankyou:relief_delivery'));

	echo elgg_view('output/longtext', [
		'value' => $entity->relief_delivery,
	]);
}

