<?php
$tags = array('a', 'p', 'div', 'span', 'img');
$allowed_styles = array('color', 'cursor', 'text-align', 'vertical-align', 'font-size',
	'font-weight', 'font-style', 'border', 'border-top', 'background-color',
	'border-bottom', 'border-left', 'border-right',
	'margin', 'margin-top', 'margin-bottom', 'margin-left',
	'margin-right', 'padding', 'float', 'text-decoration'
);
$extra_allowed_content = array();
foreach ($tags as $tag) {
	$params = array('tag' => $tag);
	$extra_allowed_content[$tag]['styles'] = elgg_trigger_plugin_hook('allowed_styles', 'htmlawed', $params, $allowed_styles);
}
$extra_allowed_content['a']['attributes'] = '!href,target,title';

$path = elgg_get_simplecache_url('ckeditor/');
$config = [
	'allowedContent' => true,
	'baseHref' => elgg_get_site_url(),
	'removePlugins' => ['tabletools', 'resize'],
	'extraPlugins' => ['blockimagepaste'],
	'defaultLanguage' => 'en',
	'language' => get_language(),
	'skin' => 'moono',
	'uiColor' => '#EEEEEE',
	'contentsCss' => elgg_get_simplecache_url('elgg/wysiwyg.css'),
	'disableNativeSpellChecker' => false,
	'disableNativeTableHandles' => false,
	'removeDialogTabs' => ['image:advanced', 'image:Link'],
	'extraAllowedContent' => $extra_allowed_content,
];

$admin_toolbar = ckeditor_addons_get_toolbar('admin');
$user_toolbar = ckeditor_addons_get_toolbar('user');

$plugins = ['blockimagepaste' => ['path' => elgg_get_simplecache_url('elgg/ckeditor/blockimagepaste.js')]];

if (ckeditor_addons_is_enabled('Tooltip')) {
	$config['extraPlugins'][] = 'tooltip';
	$plugins['tooltip'] = ['path' => elgg_get_simplecache_url('components/ckeditor/tooltip.js')];
}

$config['extraPlugins'][] = 'autogrow';

$config['extraPlugins'][] = 'resize';
$plugins['resize'] = ['path' => elgg_get_simplecache_url('components/ckeditor/plugins/resize/plugin.js')];

$config['removePlugins'] = implode(',', $config['removePlugins']);
$config['extraPlugins'] = implode(',', $config['extraPlugins']);
$config['removeDialogTabs'] = implode(';', $config['removeDialogTabs']);
?>

//<script>

	require(['elgg', 'jquery'], function (elgg, $) {

		// Reset CKEditor when form reset is triggered
		$(document).on('reset', 'form', function () {
			$(this).find('[data-cke-init]').each(function () {
				if ($(this).data('ckeditorInstance')) {
					$(this).ckeditorGet().setData('');
				}
			});
		});

		// Apply custom config
		elgg.register_hook_handler('config', 'ckeditor', function (hook, type, params, config) {
			config = config || {};
			var custom = <?php echo json_encode($config) ?>;

			custom.toolbar = elgg.is_admin_logged_in() ? <?php echo json_encode($admin_toolbar) ?> : <?php echo json_encode($user_toolbar) ?>;

			return $.extend({}, config, custom);
		});

		// Add external files and fix anchor dialog definition
		elgg.register_hook_handler('prepare', 'ckeditor', function (hook, type, params, CKEDITOR) {
			var plugins = <?php echo json_encode($plugins) ?>;
			// Add defined plugins
			$.each(plugins, function (index, plugin) {
				var names = plugin.names || index,
						path = plugin.path,
						fileName = plugin.filename || '';

				if (names && path) {
					CKEDITOR.plugins.addExternal(names, path, fileName);
				}
			});

			CKEDITOR.on('dialogDefinition', function (ev) {
				// Take the dialog name and its definition from the event data.
				var dialogName = ev.data.name;
				var dialogDefinition = ev.data.definition;

				// Check if the definition is from the dialog we are interested in (the 'link' dialog)
				if (dialogName === 'link') {

					var targetTab = dialogDefinition.getContents('target');
					var removeTargetTypes = ['frame', 'popup', '_top', '_parent', '_self'];
					targetTab.get('linkTargetType')['items'] = targetTab.get('linkTargetType')['items'].filter(function (e) {
						return removeTargetTypes.indexOf(e[1]) === -1;
					});

					var removeAttr = ['advId', 'advLangDir', 'advAccessKey',
						'advName', 'advLangCode', 'advTabIndex', 'advContentType',
						'advCSSClasses', 'advCharset', 'advStyles', 'advRel'];
					$.each(removeAttr, function (index, value) {
						dialogDefinition.getContents('advanced').remove(value);
					});
				}
			});

			return CKEDITOR;
		});
	});