<?php

namespace SBW\Campaigns;

use ElggBatch;
use hypeJunction\Payments\Transaction;

class Cron {

	public static function endCampaigns() {

		sleep(1);

		$ia = elgg_set_ignore_access(true);

		$campaigns = new ElggBatch('elgg_get_entities_from_metadata', [
			'types' => 'object',
			'subtypes' => Campaign::SUBTYPE,
			'metadata_name_value_pairs' => [
				[
					'name' => 'calendar_end',
					'value' => time(),
					'operand' => '<=',
				],
				[
					'name' => 'ended',
					'value' => false,
				],
			],
			'limit' => 0,
		]);

		foreach ($campaigns as $campaign) {
			/* @var $campaign Campaign */
			if ($campaign->end()) {
				echo PHP_EOL . "Campaign $campaign->title was ended" . PHP_EOL;
			}
		}

		elgg_set_ignore_access($ia);
	}

	public static function startCampagns() {

		sleep(1);

		$ia = elgg_set_ignore_access(true);

		$campaigns = new ElggBatch('elgg_get_entities_from_metadata', [
			'types' => 'object',
			'subtypes' => Campaign::SUBTYPE,
			'metadata_name_value_pairs' => [
				[
					'name' => 'calendar_start',
					'value' => time(),
					'operand' => '<=',
				],
				[
					'name' => 'started',
					'value' => false,
				]
			],
			'limit' => 0,
		]);

		foreach ($campaigns as $campaign) {
			/* @var $campaign Campaign */
			if ($campaign->start()) {
				echo PHP_EOL . "Campaign $campaign->title was started" . PHP_EOL;
			}
		}

		elgg_set_ignore_access($ia);
	}

	/**
	 * Sends out reminders for wire transactions after 2 and 5 days
	 * @return void
	 */
	public static function sendPaymentReminders() {

		if (!elgg_is_active_plugin('payments_wire')) {
			return;
		}

		$ia = elgg_set_ignore_access(true);

		$transactions = new ElggBatch('elgg_get_entities_from_metadata', [
			'types' => 'object',
			'subtypes' => Transaction::SUBTYPE,
			'metadata_name_value_pairs' => [
				[
					'name' => 'status',
					'value' => Transaction::STATUS_PAYMENT_PENDING,
				],
				[
					'name' => 'payment_method',
					'value' => 'wire',
				]
			],
			'limit' => 0,
		]);

		foreach ($transactions as $transaction) {
			$time_created = $transaction->time_created;
			$time_diff = (time() - $time_created) / 24 * 60 * 60;
			if (($time_diff < 2 || $time_diff >= 3) && ($time_diff < 5 || $time_diff >= 6)) {
				// no reminder needed at this time
				continue;
			}

			$customer = $transaction->getCustomer();
			if (!$customer || !$customer->email) {
				continue;
			}

			$forward_url = elgg_http_get_signed_url("payments/wire/{$transaction->getId()}");

			$merchant = $transaction->getMerchant();

			if ($merchant->wire_instructions) {
				$wire_instructions = $merchant->wire_instructions;
			} else {
				$wire_instructions = elgg_get_plugin_setting('wire_instructions', 'payments_wire');
			}

			$wire_instructions = elgg_trigger_plugin_hook('wire_instructions', 'wire', [
				'transaction' => $transaction,
			], $wire_instructions);

			$instructions = str_replace('{{invoice}}', $transaction->guid, $wire_instructions);
			$instructions = str_replace('{{amount}}', $transaction->getAmount()->format(), $instructions);
			$instructions = str_replace('{{merchant}}', $merchant->getDisplayName(), $instructions);

			$site = elgg_get_site_entity();
			$subject = elgg_echo('payments:wire:instructions:reminder', [$merchant->getDisplayName()]);
			$body = elgg_echo('payments:wire:instructions:body:reminder', [
				$transaction->getAmount()->format(),
				$merchant->getDisplayName(),
				$instructions,
				$forward_url,
			]);

			elgg_send_email($site->email, $customer->email, $subject, $body);

			echo PHP_EOL . "Payment reminder sent out for transaction with invoice No. $transaction->guid" . PHP_EOL;
		}

		elgg_set_ignore_access($ia);
	}

}
