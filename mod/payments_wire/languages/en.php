<?php

return [
	'payments:method:wire' => 'Bank transfer',
	'payments:wire:invoice' => 'Invoice No.',
	'payments:wire:invoice:id' => 'Invoice %s',

	'payments:wire:instructions' => 'Payment instructions',
	'payments:wire:instructions:help' => '
		Bank account and routing information.
		Use {{invoice}} to include the number of the invoice/transaction, e.g. as a payment reason.
		Use {{amount}} to include the amount
		Use {{merchant}} to include the final recipient
		',

	'payments:wire:instructions:body' => '
		Please use the following payment instructions to transfer %s to %s:

		%s

		You can view the details of the transaction here:
		%s
	',

	'admin:payments:payments_wire' => 'Wire Payments',
	'payments:charges:wire_fee' => 'Processing Fee',

	'payments:invoice:id' => 'Invoice %s',
];