<?php

use SBW\Campaigns\Campaign;
use SBW\Campaigns\ReliefItem;

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof Campaign) {
	return;
}

$items = elgg_get_entities([
	'types' => 'object',
	'subtypes' => ReliefItem::SUBTYPE,
	'container_guids' => (int) $entity->guid,
	'limit' => 0,
	'batch' => true,
		]);
?>
<table class="elgg-table-alt campaigns-table">
	<thead>
		<tr>
			<th><?= elgg_echo('campaigns:give:relief_item') ?></th>
			<th><?= elgg_echo('campaigns:give:quantity') ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($items as $item) {
			$id = $item->guid;
			?>
			<tr>
				<td class="campaigns-give-item-reward">
					<?php
					$icon = elgg_view_entity_icon($item, 'small', [
						'href' => false,
						'use_link' => false,
					]);
					$title = elgg_format_element('span', [
						'class' => 'campaigns-give-item-title',
							], $item->getDisplayName());

					$quantity = $item->required_quantity;
					$donated = $item->getCommitments();

					$title .= elgg_format_element('span', [
					'class' => 'campaigns-give-item-stock mll',
					],
					elgg_echo('campaigns:relief_items:required', [max(0, $quantity - $donated), $quantity])
					);

					$description = elgg_view('output/longtext', [
						'value' => elgg_get_excerpt($item->description),
						'class' => 'elgg-text-help',
					]);
					echo elgg_view_image_block($icon, $title . $description);
					?>
				</td>
				<td class="campaigns-give-item-amount">
					<?php
					echo elgg_view_field([
						'#type' => 'text',
						'name' => "amount[{$id}]",
						'value' => 0,
						'disabled' => $item->required_quantity <= $item->getCommitments(),
						'max' => $item->required_quantity - $item->getCommitments()
					]);
					?>
				</td>
			</tr>
			<?php
		}
		?>
	</tbody>
</table>