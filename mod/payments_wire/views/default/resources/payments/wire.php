<?php

use hypeJunction\Payments\Transaction;

elgg_signed_request_gatekeeper();

$transaction = elgg_extract('entity', $vars);
if (!$transaction instanceof Transaction) {
	forward('', '404');
}

$title = elgg_echo('payments:invoice:id', [$transaction->guid]);

$instructions = str_replace('{{invoice}}', $transaction->guid, $transaction->wire_instructions);
$instructions = str_replace('{{amount}}', $transaction->getAmount()->format(), $instructions);
$instructions = str_replace('{{merchant}}', $transaction->getMerchant()->title, $instructions);

$instructions = elgg_view('output/longtext', [
	'value' => $instructions,
]);

$content = elgg_view_module('aside', elgg_echo('payments:wire:instructions'), $instructions);

$content .= elgg_view_entity($transaction, [
	'full_view' => true,
]);

$layout = elgg_view_layout('content', [
	'content' => $content,
	'title' => $title,
	'filter' => false,
]);

echo elgg_view_page($title, $layout);

