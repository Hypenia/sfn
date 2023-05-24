<?php

use hypeJunction\Payments\Amount;
use SBW\Campaigns\Campaign;
use SBW\Campaigns\ReliefItem;

$entity = elgg_extract('entity', $vars);

if (!$entity instanceof Campaign) {
	return;
}

$data = [];

$data['model'] = [
	'icon' => 'line-chart',
	'label' => elgg_echo('campaigns:field:model'),
	'value' => elgg_echo("campaigns:model:$entity->model"),
];

if ($entity->model == Campaign::MODEL_RELIEF) {
	$items = elgg_get_entities([
		'types' => 'object',
		'subtypes' => ReliefItem::SUBTYPE,
		'container_guids' => (int) $entity->guid,
		'limit' => 0,
		'batch' => true,
	]);

	foreach ($items as $item) {
		$data[$item->title] = [
			'icon' => 'life-ring',
			'label' => elgg_echo('campaigns:field:relief_item'),
			'value' => strtolower("$item->required_quantity $item->title"),
		];
	}
} else {
	$minimum = (new Amount($entity->donation_minimum, $entity->currency))->getConvertedAmount();
	$unit = $entity->currency;

	$data['minimum_donation'] = [
		'icon' => 'usd',
		'label' => elgg_echo('campaigns:field:donation_minimum'),
		'value' => "$minimum $unit",
	];

	$minimum = (new Amount($entity->target_amount, $entity->currency))->getConvertedAmount();
	$unit = $entity->currency;

	$data['target_amount'] = [
		'icon' => 'bullseye',
		'label' => elgg_echo('campaigns:field:target_amount'),
		'value' => "$minimum $unit",
	];
}

//if (date('Y', $entity->calendar_start) == date('Y', $entity->calendar_end)) {
//	$start_format = 'M j';
//} else {
//	$start_format = 'M j, Y';
//}
//$period = elgg_echo('campaigns:funding_period', [
//	date($start_format, $entity->calendar_start),
//	date('M j, Y', $entity->calendar_end)
//		]);

$period = date('M j, Y', $entity->calendar_end);

$data['funding_period'] = [
	'icon' => 'calendar',
	'label' => elgg_echo('campaigns:field:funding_period'),
	'value' => $period,
];

if ($entity->location) {
	$data['location'] = [
		'icon' => 'map-marker',
		'label' => elgg_echo('campaigns:field:location'),
		'value' => $entity->location,
	];
}

if ($entity->website) {
	$data['link'] = [
		'icon' => 'globe',
		'label' => elgg_echo('campaigns:field:website'),
		'value' => elgg_view('output/url', [
			'text' => parse_url($entity->website, PHP_URL_HOST),
			'href' => $entity->website,
		]),
	];
}

if (elgg_extract('full_view', $vars, false)) {
	$data['tags'] = [
		'icon' => 'tag',
		'label' => elgg_echo('campaigns:field:tags'),
		'value' => elgg_view('output/tags', [
			'entity' => $entity,
			'icon_class' => 'hidden',
		]),
	];
}

foreach ($data as $item) {
	?>
	<div class="campaigns-data-item">
		<?php
		echo elgg_view_icon($item['icon'], [
			'title' => $item['label'],
		]);
		echo elgg_format_element('span', [
			'class' => 'campaigns-data-item-value',
				], $item['value']);
		?>
	</div>
	<?php
}
