<?php

use SBW\Campaigns\Commitment;
use SBW\Campaigns\ReliefItem;

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof ReliefItem) {
	return;
}

$required = (int) $entity->required_quantity;
$committed = (int) $entity->getCommitments();
$backers = (int) $entity->countAnnotations('committed');

if ($required) {
	$funded_percentage = round($committed * 100/ $required);
} else {
	$funded_percentage = 100;
}
$count = elgg_format_element('span', [
	'class' => 'campaigns-stats-counter',
		], "{$funded_percentage}%");
$funded = elgg_echo('campaigns:committed', [$count]);

$count = elgg_format_element('span', [
	'class' => 'campaigns-stats-counter',
		], "{$committed} / {$required}");
$total = elgg_echo('campaigns:committed:items', [$count]);

$count = elgg_format_element('span', [
	'class' => 'campaigns-stats-counter',
		], "{$backers}");
$backers = elgg_echo('campaigns:backers', [$count]);
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
	</div>
</div>