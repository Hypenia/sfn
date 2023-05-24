<?php

elgg_signed_request_gatekeeper();

$ia = elgg_set_ignore_access();

$commitment_guid = get_input('d');
$commitment = get_entity($commitment_guid);

if ($commitment instanceof SBW\Campaigns\Commitment) {
	$commitment->setStatus(SBW\Campaigns\Commitment::STATUS_CONFIRMED);
	system_message(elgg_echo('campaigns:commitment:confirm:success'));
}

elgg_set_ignore_access($ia);

$campaign_guid = get_input('c');

forward("campaigns/view/$campaign_guid");

