<?php

require_once __DIR__ . '/autoloader.php';

use SBW\Campaigns\Balance;
use SBW\Campaigns\Campaign;
use SBW\Campaigns\Commitment;
use SBW\Campaigns\Contribution;
use SBW\Campaigns\Donation;
use SBW\Campaigns\Donor;
use SBW\Campaigns\NewsItem;
use SBW\Campaigns\ReliefItem;
use SBW\Campaigns\Reward;

$subtypes = [
	Campaign::SUBTYPE => Campaign::class,
	Reward::SUBTYPE => Reward::class,
	Contribution::SUBTYPE => Contribution::class,
	Balance::SUBTYPE => Balance::class,
	Donor::SUBTYPE => Donor::class,
	Donation::SUBTYPE => Donation::class,
	NewsItem::SUBTYPE => NewsItem::class,
	ReliefItem::SUBTYPE => ReliefItem::class,
	Commitment::SUBTYPE => Commitment::class,
];

foreach ($subtypes as $subtype => $class) {
	if (!update_subtype('object', $subtype, $class)) {
		add_subtype('object', $subtype, $class);
	}
}
