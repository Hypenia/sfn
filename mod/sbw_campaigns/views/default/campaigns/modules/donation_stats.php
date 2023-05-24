<?php

use hypeJunction\Payments\Amount;
use SBW\Campaigns\Campaign;
use SBW\Campaigns\Donation;

$entity = elgg_extract('entity', $vars);

if (!$entity instanceof Campaign) {
	return;
}

$sum_donation = 0;
$count_donation = 0;
$donations = elgg_get_entities([
	'types' => 'object',
	'subtypes' => Donation::SUBTYPE,
	'container_guids' => $entity->guid,
	'limit' => 0,
	'batch' => true,
		]);
foreach ($donations as $donation) {
	$count_donation++;
	$sum_donation += $donation->net_amount;
}

$avg_donation = 0;
if ($count_donation) {
	$avg_donation = (int) round($sum_donation / $count_donation);
}

$count = elgg_format_element('span', [
	'class' => 'campaigns-stats-counter',
		], (new Amount($avg_donation, $entity->currency))->format());
$avg_donation = elgg_echo('campaigns:avg_donation', [$count]);

$count = elgg_format_element('span', [
	'class' => 'campaigns-stats-counter',
		], $count_donation);
$count_donation = elgg_echo('campaigns:donations:total', [$count]);
?>

<div class="campaigns-stats">
	<div class="campaigns-stats-counters">
		<div><?= $count_donation ?></div>
		<div><?= $avg_donation ?></div>
	</div>
</div>