<?php

use hypeJunction\Payments\Amount;
use SBW\Campaigns\Campaign;
use SBW\Campaigns\Commitment;
use SBW\Campaigns\ReliefItem;

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof Campaign) {
	return;
}

$diff = $entity->calendar_end - time();

if ($diff > 24 * 60 * 60) {
	$count = (int) floor($diff / (24 * 60 * 60));
	$count = elgg_format_element('span', [
		'class' => 'campaigns-stats-counter',
			], $count);
	$days = elgg_echo('campaigns:ends:in_days', [$count]);
} else if ($diff > 60 * 60) {
	$count = (int) floor($diff / (60 * 60));
	$count = elgg_format_element('span', [
		'class' => 'campaigns-stats-counter',
			], $count);
	$days = elgg_echo('campaigns:ends:in_hours', [$count]);
} else if ($diff > 0) {
	$count = (int) floor($diff / 60);
	$count = elgg_format_element('span', [
		'class' => 'campaigns-stats-counter',
			], $count);
	$days = elgg_echo('campaigns:ends:in_minutes', [$count]);
} else {
	$days = '';
}

$funded_percentage = round((float) $entity->funded_percentage);
$backers = (int) $entity->backers;
$count = elgg_format_element('span', [
	'class' => 'campaigns-stats-counter',
		], "{$backers}");
$backers = elgg_echo('campaigns:backers', [$count]);

if ($entity->model == Campaign::MODEL_RELIEF) {
	$count = elgg_format_element('span', [
		'class' => 'campaigns-stats-counter',
			], "{$funded_percentage}%");
	$funded = elgg_echo('campaigns:committed', [$count]);

	$count = elgg_format_element('span', [
		'class' => 'campaigns-stats-counter',
			], "{$entity->committed_quantity}");
	$total = elgg_echo('campaigns:committed:items', [$count]);
} else {

	$count = elgg_format_element('span', [
		'class' => 'campaigns-stats-counter',
			], "{$funded_percentage}%");
	$funded = elgg_echo('campaigns:funded', [$count]);

	$amount = (new Amount((int) $entity->net_amount, $entity->currency))->getConvertedAmount();
	$amount = round($amount);
	
	$count = elgg_format_element('span', [
		'class' => 'campaigns-stats-counter',
			], "$amount $entity->currency");
	$total = elgg_echo('campaigns:funded:total', [$count]);
}
?>
<div class="campaigns-stats">
	<div class="campaigns-progress-container"
		 title="<?= elgg_echo('campaigns:funded_percentage', ["{$funded_percentage}%"]) ?>">
		<div class="campaigns-progress-bar"
			 style="width:<?= $funded_percentage ?>%"
			 ></div>
	</div>
	<div class="campaigns-stats-counters">
		<div><?= $funded ?></div>
		<div><?= $total ?></div>
		<div><?= $backers ?></div>
		<div><?= $days ?></div>
	</div>
</div>