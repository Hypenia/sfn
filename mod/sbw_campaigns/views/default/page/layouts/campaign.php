<?php
$class = 'elgg-layout campaigns-profile-layout clearfix';
if (isset($vars['class'])) {
	$class = "$class {$vars['class']}";
}

$sidebar = elgg_extract('sidebar', $vars);
$filter = elgg_extract('filter', $vars, '');

if ($sidebar) {
	$class .= ' campaigns-profile-layout-has-sidebar';
}
if ($filter) {
	$class .= ' campaigns-profile-layout-has-filter';
}
?>


<div class="<?php echo $class; ?>">
	<?php
	echo elgg_extract('nav', $vars, elgg_view('navigation/breadcrumbs'));

	echo elgg_view('page/layouts/elements/header', $vars);
	?>
	<div class="elgg-main elgg-body">
		<div class="elgg-module campaigns-profile">
			<div class="campaigns-main">
				<?php
				echo $filter;
				?>
				<div class="campaigns-main-content">
					<?php
					echo elgg_extract('content', $vars, '');
					?>
				</div>
			</div>
			<?php
			if ($sidebar) {
				?>
				<div class="campaigns-sidebar">
					<?= $sidebar ?>
				</div>
				<?php
			}
			?>
		</div>
	</div>
	<?php
	echo elgg_view('page/layouts/elements/footer', $vars);
	?>
</div>