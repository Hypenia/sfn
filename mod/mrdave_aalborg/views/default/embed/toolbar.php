<?php
/**
 * @uses $vars['embeds'] Disable embed by setting to false
 */
?>

<div class="embed-toolbar">
	<?php
// 	if (elgg_extract('embeds', $vars, true)) {
// 		echo elgg_view_menu('embed', [
// 			'sort_by' => 'priority',
// 			'textarea_id' => elgg_extract('id', $vars),
// 		]);
// 	}

	echo elgg_view_menu('longtext', [
		'sort_by' => 'priority',
		'class' => 'elgg-menu-embed',
		'id' => elgg_extract('id', $vars),
	]);
	?>

    <div class="elgg-module-popup embed-toolbar-popup"></div>
</div>

<script>require(['elgg/embed', 'embed/toolbar'])</script>