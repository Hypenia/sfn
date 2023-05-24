<?php

$tabs = [];

$languages = array_keys(get_installed_translations());

$entity = elgg_extract('entity', $vars);

$value = elgg_extract('value', $vars);
if (!is_array($value)) {
	$value = [];
	if ($entity) {
		foreach ($languages as $lang) {
			if ($lang != 'en' && get_language_completeness($lang) < 50) {
				continue;
			}

			switch ($lang) {
				case 'en' :
					$value[$lang] = $entity->description;
					break;

				default :
					$value[$lang] = $entity->{"description_{$lang}"};
					break;
			}
		}
	}
}

foreach ($languages as $lang) {

	if ($lang != 'en' && get_language_completeness($lang) < 50) {
		continue;
	}

	if (elgg_view_exists("language_selector/flags/$lang.gif")) {
		$label = elgg_view('output/img', [
			'src' => elgg_get_simplecache_url("language_selector/flags/$lang.gif"),
			'alt' => elgg_echo($lang),
		]);
	} else {
		$label = elgg_echo($lang);
	}

	$field_vars = $vars;
	$field_vars['required'] = $vars['required'] && $lang !== 'en';
	$field_vars['name'] = "{$vars['name']}[$lang]";
	$field_vars['value'] = elgg_extract($lang, $value);

	$tabs[] = [
		'text' => $label,
		'content' => elgg_view("input/longtext", $field_vars),
		'selected' => get_current_language() == $lang,
	];
}

echo elgg_view('page/components/tabs', [
	'tabs' => $tabs,
]);