<?php

namespace MrDave\AalborgExtras;

use ElggMenuItem;

elgg_register_event_handler('init', 'system', __NAMESPACE__ . '\\init_late', 1000);

function init_late() {
    // re-register embed menu after hypeEmbed
    elgg_register_plugin_hook_handler('register', 'menu:longtext', 'embed_longtext_menu');
    
	elgg_extend_view('css/elgg', 'css/mrdave_aalborg');

	elgg_register_css('fonts.Open Sans', 'https://fonts.googleapis.com/css?family=Open+Sans:400,300&subset=latin,latin-ext');
	elgg_load_css('fonts.Open Sans');

	elgg_require_js('mrdave_aalborg');
	
	if (elgg_is_active_plugin('sbw_campaigns')) {elgg_require_js('moment');}

	// add a topbar in the header
	// TODO leave topbar in place and change with CSS
	elgg_extend_view('page/elements/header', 'mrdave_aalborg/topbar', 0);

	elgg_extend_view('forms/comment/save', 'mrdave_aalborg/comment_save', 0);
	
	// topbar menu edits
	elgg_register_plugin_hook_handler('prepare', 'menu:topbar', 'MrDave\AalborgExtras\Topbar::prepareMenu', 1000);

	// user hover customizations
	elgg_register_plugin_hook_handler('prepare', 'menu:user_hover', 'MrDave\AalborgExtras\UserHover::prepareMenu', 1000);

	// https://github.com/Elgg/Elgg/issues/8718
	elgg_register_plugin_hook_handler('route', 'file', 'MrDave\AalborgExtras\Files::handleFileRoute', 1000);
	elgg_register_plugin_hook_handler('register', 'menu:page', 'MrDave\AalborgExtras\Files::registerPageMenu', 1000);
	elgg_register_plugin_hook_handler('register', 'menu:extras', 'MrDave\AalborgExtras\Files::registerExtrasMenu', 1000);

	// Replace "Navigation" with "Pages"
	// TODO cleanup
	elgg_register_plugin_hook_handler('view', 'pages/sidebar/navigation', function($h, $t, $v, $p) {
		$v = preg_replace('~<h3>.*?</h3>~', '<h3>' . elgg_echo('pages') . '</h3>', $v, 1);
		return $v;
	});

	// https://github.com/Elgg/Elgg/issues/8697
	elgg_unextend_view('profile/status', 'thewire/profile_status');
	
    elgg_unregister_plugin_hook_handler('head', 'page', 'aalborg_theme_setup_head');
	elgg_register_plugin_hook_handler('head', 'page', function($hook, $type, $data) {
	    $data['metas']['viewport'] = array(
		    'name' => 'viewport',
		    'content' => 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0',
	    );

	    $data['links']['apple-touch-icon'] = array(
		    'rel' => 'apple-touch-icon',
		    'href' => elgg_get_simplecache_url('mrdave_aalborg/homescreen.png'),
	    );

	    return $data;
    });
	// prepare profile buttons to be registered in the title menu
	elgg_register_plugin_hook_handler('profile_buttons', 'group', function($type, $subtype, $items, $params) {

    if (elgg_is_logged_in()) {
        $entity = $params['entity'];

        $entity_cover_setting = elgg_get_plugin_setting('amap_coverphoto_user', 'amap_coverphoto');

        if (elgg_instanceof($entity, 'group') && $entity->canEdit() && $entity_cover_setting == AMAP_COVERPHOTO_GENERAL_YES) {
            $url = elgg_normalize_url("coverphoto/edit/" . $entity->guid);

            //$url = elgg_add_action_tokens_to_url($url);	
            //$menuItem = new ElggMenuItem('editcoverphoto', elgg_echo('amap_coverphoto:edit:cover'), $url);
            //$menuItem->setSection('action');
            //$return_value [] = $menuItem;
			
		$items[] = ElggMenuItem::factory(array(
			'name' => 'editcoverphoto',
			'href' => $url,
			'text' => elgg_echo('amap_coverphoto:edit:cover'),
			//'is_action' => 0 === strpos($url, 'action'),
			'link_class' => 'elgg-button elgg-button-action',
		));
	    }

	return $items;
    }
});

	// https://github.com/Elgg/Elgg/issues/8628
	if (version_compare(elgg_get_version(), '2.0', '>=')) {
		// https://github.com/Elgg/Elgg/issues/8628
		elgg_unregister_menu_item('extras', 'report_this');
		elgg_unregister_menu_item('footer','powered');

		if (elgg_is_logged_in()) {
			elgg_register_menu_item('extras', array(
				'name' => 'report_this',
				'href' => 'reportedcontent/add',
				'title' => elgg_echo('reportedcontent:this:tooltip'),
				'text' => elgg_view_icon('exclamation-triangle'),
				'priority' => 500,
				'section' => 'default',
				'link_class' => 'elgg-lightbox',
			));
		}

	// Extend footer with copyright
	$site_name = elgg_get_site_entity()->name;
	$year = date('Y');	
	elgg_register_menu_item('footer', array(
		'name' => 'copyright_this',
		'href' => '/',
		'text' => $site_name . elgg_echo('sfn:copyright') . $year,
		'priority' => 1,
		'section' => 'alt',
	));
	
	// Extend footer with  daveonche™ LLC signature
	$href = "mailto:daveonche@gmail.com";
	elgg_register_menu_item('footer', array(
		'name' => 'designed_by',
		'href' => $href,
		'title' => elgg_echo('Web Software Developer'),
		'text' => elgg_echo('Designed with ♡ by') . elgg_echo(' <b>daveonche™ LLC</b>'),
		'priority' => 2,
		'section' => 'alt',
	));	
	    }

	$path = substr(current_page_url(), strlen(elgg_get_site_url()));

	// remove duplicate title from page view
	// TODO cleanup
	if (preg_match('~^pages/view/(\d+)~', $path, $m)) {
		$guid = (int)$m[1];

		// https://github.com/Elgg/Elgg/issues/8723
		elgg_register_plugin_hook_handler('view_vars', 'object/elements/summary', function ($h, $t, $vars, $p) use ($guid) {
			if (empty($vars['entity'])) {
				return;
			}
			$entity = $vars['entity'];
			/* @var \ElggEntity $entity */

			// make sure this is the expected entity
			if ($entity->guid !== $guid) {
				return;
			}
			$vars['title'] = false;
			return $vars;
		});

		// https://github.com/Elgg/Elgg/issues/8722
		elgg_register_plugin_hook_handler('view_vars', 'object/elements/full', function ($h, $t, $vars, $p) use ($guid) {
			// make sure this is the expected entity
			if (empty($vars['entity'])) {
				return;
			}
			$entity = $vars['entity'];
			/* @var \ElggEntity $entity */

			if ($entity->guid !== $guid) {
				return;
			}
			$vars['icon'] = elgg_view_entity_icon($entity->getOwnerEntity(), 'tiny');
			return $vars;
		});
	}

//	// add resources view classes to BODY
//	elgg_register_plugin_hook_handler('view_vars', 'all', function ($h, $view, $vars, $p) {
//		if (0 !== strpos($view, 'resources/')) {
//			return;
//		}
//
//		$classes = [];
//
//		$classes[] = 'elgg-' . preg_replace('~[^a-zA-Z0-9]+~', '-', $view);
//
//		if (is_array($vars)) {
//			foreach ($vars as $key => $value) {
//				if (is_string($value)
//						&& preg_match('~^[a-zA-Z0-9_]+$~', $key)
//						&& preg_match('~^[a-zA-Z0-9_]+$~', $key)) {
//					$classes[] = "elgg-resource-vars-$key-$value";
//				}
//			}
//		}
//
//		$classes_adder = function ($h, $t, $vars, $p) use ($classes) {
//			$body_attrs = (array)elgg_extract('body_attrs', $vars, []);
//			$body_classes = (array)elgg_extract('class', $body_attrs, []);
//
//			array_splice($body_classes, count($body_classes), 0, $classes);
//			$vars['body_attrs']['class'] = $body_classes;
//
//			return $vars;
//		};
//		elgg_register_plugin_hook_handler('view_vars', 'page/elements/html', $classes_adder);
//	});
}

