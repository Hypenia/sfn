<?php

echo elgg_view('input/campaigns/billing_address', $vars);

echo elgg_view('output/longtext', [
	'value' => elgg_echo('campaigns:payment:paypal:help'),
]);
