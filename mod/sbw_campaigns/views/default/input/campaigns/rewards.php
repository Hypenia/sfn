<?php

use SBW\Campaigns\Campaign;
use SebastianBergmann\Money\Currency;
use SebastianBergmann\Money\Money;

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof Campaign) {
	return;
}
$selected = elgg_extract('value', $vars, 'no_reward');
$rewards = $entity->getRewards();
?>
<script>
	require(['forms/campaigns/give']);
</script>

<table class="elgg-table-alt campaigns-table">
	<tbody>
		<tr <?= $tr_attrs ?>>
			<td class="campaigns-give-column-picker">
				<?php
				echo elgg_view('input/radio', [
					'name' => 'reward',
					'value' => $selected,
					'class' => 'campaigns-give-reward-picker',
					'options' => [
						'' => 'no_reward',
					],
				]);
				?>
			</td>
			<td class="campaigns-give-item-reward">
				<?php
				echo elgg_format_element('span', [
					'class' => 'campaigns-give-item-title',
						], elgg_echo('campaigns:give:no_reward'));

				$currency = new Currency($entity->currency);
				$minimum = (new Money($entity->donation_minimum, $currency))->getConvertedAmount();
				$unit = $currency->getCurrencyCode();

				echo elgg_format_element('span', [
					'class' => 'campaigns-give-item-minimimum-donation',
						], "$minimum $unit");
				?>
			</td>
			<td class="campaigns-give-item-amount">
				<?php
				echo elgg_view_field([
					'#type' => 'text',
					'#label' => elgg_echo('campaigns:give:amount'),
					'#class' => $selected == 'no_reward' ? 'campaigns-give-donation-amount' : 'campaigns-give-donation-amount hidden',
					'name' => "amount[no_reward]",
					'value' => $minimum,
					'min' => $minimum,
				]);
				?>
			</td>
		</tr>
		<?php
		foreach ($rewards as $reward) {
			$id = $reward->getId();
			$stock = $reward->getStock();
			?>
			<tr <?= $tr_attrs ?>>
				<td class="campaigns-give-column-picker">
					<?php
					echo elgg_view('input/radio', [
						'name' => 'reward',
						'value' => $selected,
						'class' => 'campaigns-give-reward-picker',
						'disabled' => !$stock,
						'options' => [
							'' => $id,
						],
					]);
					?>
				</td>
				<td class="campaigns-give-item-reward">
					<?php
					$icon = elgg_view_entity_icon($reward, 'small', [
						'href' => false,
						'use_link' => false,
					]);

					$title = elgg_format_element('span', [
						'class' => 'campaigns-give-item-title',
							], $reward->getDisplayName());

					$currency = new Currency($entity->currency);
					$minimum = (new Money($reward->donation_minimum, $currency))->getConvertedAmount();
					$unit = $currency->getCurrencyCode();

					$title .= elgg_format_element('span', [
						'class' => 'campaigns-give-item-minimimum-donation',
							], "$minimum $unit");
					if (!$stock) {
						$title .= elgg_format_element('span', [
							'class' => 'campaigns-give-item-out',
								], elgg_echo('campaigns:rewards:out_of_stock'));
					} else {
						$title .= elgg_format_element('span', [
							'class' => 'campaigns-give-item-stock',
								], elgg_echo('campaigns:rewards:in_stock', [$stock]));
					}
					$description = elgg_view('output/longtext', [
						'value' => elgg_get_excerpt($reward->description),
						'class' => 'elgg-text-help',
					]);
					echo elgg_view_image_block($icon, $title . $description);
					?>
				</td>
				<td class="campaigns-give-item-amount">
					<?php
					if ($stock) {
						echo elgg_view_field([
							'#type' => 'text',
							'#label' => elgg_echo('campaigns:give:amount'),
							'#class' => $selected == $id ? 'campaigns-give-donation-amount' : 'campaigns-give-donation-amount hidden',
							'name' => "amount[{$id}]",
							'value' => $minimum,
							'min' => $minimum,
						]);
					}
					?>
				</td>
			</tr>
			<?php
		}
		?>
	</tbody>
</table>