<?php
$entity = elgg_extract('entity', $vars);

$defaults = ckeditor_addons_get_toolbar_defaults();
$toolbar = ckeditor_addons_get_toolbar_options();

$toolbar_config = $entity->toolbar_config;
if (!$toolbar_config) {
	$toolbar_config = array(
		'admin' => $defaults,
		'user' => $defaults,
	);
} else {
	$toolbar_config = unserialize($toolbar_config);
}
?>

<table class="elgg-table-alt">
	<thead>
		<tr>
			<th>&nbsp;</th>
			<th><?php echo elgg_echo('ckeditor:settings:user') ?></th>
			<th><?php echo elgg_echo('ckeditor:settings:admin') ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($toolbar as $group => $buttons) {
			?>
			<tr>
				<td colspan="3">
					<?php echo '<h3>' . elgg_echo("ckeditor:$group") . '</h3>' ?>
				</td>
			</tr>
			<?php foreach ($buttons as $button) {
				?>
				<tr>
					<td><?php echo $button ?></td>
					<td><?php
						echo elgg_view('input/checkbox', array(
							'name' => "params[toolbar_config][user][]",
							'value' => $button,
							'default' => false,
							'checked' => in_array($button, $toolbar_config['user']),
						));
						?>
					</td>
					<td><?php
						echo elgg_view('input/checkbox', array(
							'name' => "params[toolbar_config][admin][]",
							'value' => $button,
							'default' => false,
							'checked' => in_array($button, $toolbar_config['admin']),
						));
						?>
					</td>
				</tr>
				<?php
			}
			?>

			<?php
		}
		?>
	</tbody>
</table>