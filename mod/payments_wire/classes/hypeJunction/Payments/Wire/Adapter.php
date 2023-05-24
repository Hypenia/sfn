<?php

namespace hypeJunction\Payments\Wire;

use hypeJunction\Payments\GatewayInterface;
use hypeJunction\Payments\TransactionInterface;

class Adapter implements GatewayInterface {

	public $wire_instructions;
	
	/**
	 * {@inheritdoc}
	 */
	public function pay(TransactionInterface $transaction) {

		$transaction->setStatus(TransactionInterface::STATUS_PAYMENT_PENDING);
		$transaction->wire_instructions = $this->wire_instructions;

		$forward_url = elgg_http_get_signed_url("payments/wire/{$transaction->getId()}");
		$response = elgg_redirect_response($forward_url);

		$customer = $transaction->getCustomer();
		if (!$customer || !$customer->email) {
			return $response;
		}

		$merchant = $transaction->getMerchant();
		
		$instructions = str_replace('{{invoice}}', $transaction->guid, $this->wire_instructions);
		$instructions = str_replace('{{amount}}', $transaction->getAmount()->format(), $instructions);
		$instructions = str_replace('{{merchant}}', $transaction->getMerchant()->title, $instructions);
		
		$site = elgg_get_site_entity();
		$subject = elgg_echo('payments:wire:instructions');
		$body = elgg_echo('payments:wire:instructions:body', [
			$transaction->getAmount()->format(),
			$merchant->getDisplayName(),
			$instructions,
			$forward_url,
		]);

		elgg_send_email($site->email, $customer->email, $subject, $body);

		return $response;
	}
	
	public function setWireInstructions($wire_instructions) {
		$this->wire_instructions = $wire_instructions;
	}

	/**
	 * {@inheritdoc}
	 */
	public function refund(TransactionInterface $transaction) {
		$transaction->setStatus(TransactionInterface::STATUS_REFUND_PENDING);
		return true;
	}

}
