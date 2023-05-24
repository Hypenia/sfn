<?php

require_once __DIR__ . '/autoloader.php';

elgg_register_event_handler('init', 'system', 'ckeditor_addons_init');

/**
 * Initialize the plugin
 * @return void
 */
function ckeditor_addons_init() {

	elgg_register_simplecache_view('components/ckeditor/setup.js');
	elgg_extend_view('elgg.js', 'components/ckeditor/setup.js');

	elgg_register_action('ckeditor_addons/settings/save', __DIR__ . '/actions/settings/save.php', 'admin');
}

/**
 * Returns toolbar settings
 *
 * @param string $type User type 'admin'|'user'
 * @return array
 */
function ckeditor_addons_get_toolbar($type = null) {
	$toolbar_config = elgg_get_plugin_setting('toolbar_config', 'ckeditor_addons');
	if ($toolbar_config) {
		$toolbar_config = unserialize($toolbar_config);
	} else {
		$toolbar_config = array();
	}

	if (!$type) {
		$type = (elgg_is_admin_logged_in()) ? 'admin' : 'user';
	}

	$toolbar = elgg_extract($type, $toolbar_config, ckeditor_addons_get_toolbar_defaults());

	$options = ckeditor_addons_get_toolbar_options();
	foreach ($options as $group => $buttons) {
		foreach ($buttons as $key => $button) {
			if (!in_array($button, $toolbar)) {
				unset($options[$group][$key]);
			}
		}
		if (empty($options[$group])) {
			unset($options[$group]);
		} else {
			$options[$group] = array_values($options[$group]);
		}
	}

	return array_values($options);
}

/**
 * Returns buttons enabled by default
 * @return array
 */
function ckeditor_addons_get_toolbar_defaults() {
	$defaults = ['Bold', 'Italic', 'Underline', 'RemoveFormat', 'Strike', 'NumberedList', 'BulletedList', 'Undo', 'Redo', 'Link', 'Unlink', 'Image', 'Blockquote', 'Paste', 'PasteFromWord', 'Maximize'];
	return elgg_trigger_plugin_hook('toolbar:defaults', 'ckeditor', null, $defaults);
}

/**
 * Returns toolbar buttons by group
 * @return array
 */
function ckeditor_addons_get_toolbar_options() {
	$options = [
		'basicstyles' => ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', 'RemoveFormat'],
		'paragraph' => ['NumberedList', 'BulletedList', 'Outdent', 'Indent', 'Blockquote', 'CreateDiv', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', 'BidiLtr', 'BidiRtl', 'Language'],
		'clipboard' => ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', 'Undo', 'Redo'],
		'editing' => ['Find', 'Replace', 'SelectAll', 'Scayt'],
		'links' => ['Link', 'Unlink', 'Anchor'],
		'insert' => ['Image', 'Tooltip', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe'],
		'styles' => ['Styles', 'Format', 'Font', 'FontSize'],
		'colors' => ['TextColor', 'BGColor'],
		'tools' => ['Maximize', 'ShowBlocks'],
		'document' => ['Source', 'Templates'],
	];

	return elgg_trigger_plugin_hook('toolbar:options', 'ckeditor', null, $options);
}

/**
 * Checks if given addon is enabled
 *
 * @param string $button Button
 * @param string $type   User type
 * @return boolean
 */
function ckeditor_addons_is_enabled($button = '', $type = null) {

	$options = ckeditor_addons_get_toolbar($type);
	foreach ($options as $group => $buttons) {
		if (in_array($button, $buttons)) {
			return true;
		}
	}
	return false;
}
