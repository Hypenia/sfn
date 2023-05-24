<?php

use SBW\Campaigns\Balance;
use SBW\Campaigns\Campaign;

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof Campaign) {
	return;
}

$balance = elgg_list_entities_from_relationship([
	'types' => 'object',
	'subtypes' => Balance::SUBTYPE,
	'container_guids' => $entity->guid,
	'list_type' => 'table',
	'columns' => [
		elgg()->table_columns->transaction_id(),
		elgg()->table_columns->time_created(null, [
			'format' => 'M j, Y H:i',
		]),
		elgg()->table_columns->payment_method(),
		elgg()->table_columns->customer(),
		elgg()->table_columns->merchant(),
		elgg()->table_columns->amount(),
		elgg()->table_columns->payment_status(),
	],
	'item_class' => 'payments-transaction',
	'no_results' => function() use ($entity) {
		if ($entity->ended) {
			return elgg_echo('campaigns:edit:balance:target_not_reached');
		} else {
			return elgg_echo('campaigns:edit:balance:no_results');
		}
	},
	'limit' => 0,
		]);

echo elgg_view_module('aside', elgg_echo('campaigns:balance'), $balance);

$form = elgg_view_form('campaigns/edit/payout', [], $vars);

echo elgg_view_module('aside', elgg_echo('campaigns:payout:details'), $form);