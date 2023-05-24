<?php

namespace hypeJunction\Discovery;

class Router {

	/**
	 * Handle incoming discovery traffic
	 *
	 * @param array $segments URL segments
	 * @return boolean
	 */
	public static function permalinkHandler($segments) {

		$viewtype = array_shift($segments);
		$referrer_hash = array_shift($segments);
		if (!preg_match('/^[a-f0-9]{32}$/i', $referrer_hash)) {
			// The hash was moved into URL query parameters
			$guid = $referrer_hash;
			$referrer_hash = get_input('uh');
		} else {
			$guid = array_shift($segments);
			set_input('uh', $referrer_hash);
		}

		switch ($viewtype) {
			case 'image' :
				// BC router
				$size = array_shift($segments);
				$ia = elgg_set_ignore_access(true);
				$entity = get_entity($guid);
				$url = $entity->getIconURL($size);
				elgg_set_ignore_access($ia);
				forward($url);
				return;

			default :

				switch ($viewtype) {
					case 'json+oembed' :
					case 'json oembed' :
						$viewtype = 'json';
						break;

					case 'xml+oembed' :
					case 'xml oembed' :
						$viewtype = 'xml';
						break;
				}

				if (!elgg_is_registered_viewtype($viewtype)) {
					$viewtype = 'default';
				}

				elgg_set_viewtype($viewtype);

				if (!$guid || !elgg_entity_exists($guid)) {
					return false;
				}

				$ia = elgg_set_ignore_access();
				$entity = get_entity($guid);

				if (!has_access_to_entity($entity) && !is_discoverable($entity)) {
					elgg_set_ignore_access($ia);
					return false;
				}

				elgg_register_plugin_hook_handler('head', 'page', function($hook, $type, $return) use ($entity) {
					if (isset($return['links']['canonical'])) {
						return;
					}

					if (elgg_is_active_plugin('hypeSeo')) {
						$svc = \hypeJunction\Seo\RewriteService::getInstance();
						$data = $svc->getRewriteRulesFromGUID($entity->getURL());
						if (isset($data['sef_path'])) {
							$return['links']['canonical'] = [
								'href' => elgg_normalize_url($data['sef_path']),
								'rel' => 'canonical',
							];
							return $return;
						}
					}

					$return['links']['canonical'] = [
						'href' => $entity->getURL(),
						'rel' => 'canonical',
					];

					return $return;
				});

				$forward_url = false;

				$is_walled = elgg_get_config('walled_garden') && !elgg_is_logged_in();
				if (has_access_to_entity($entity) && $viewtype == 'default' && !$is_walled) {
					$forward_url = $entity->getURL();
				}

				$forward_url = elgg_trigger_plugin_hook('entity:referred', $entity->getType(), array(
					'entity' => $entity,
					'user_hash' => $referrer_hash,
					'referrer' => $_SERVER['HTTP_REFERER'],
						), $forward_url);

				if ($forward_url) {
					elgg_set_ignore_access($ia);
					forward($forward_url);
				}

				echo elgg_view_resource('permalink', [
					'viewtype' => $viewtype,
					'user_hash' => $referrer_hash,
					'guid' => $guid,
					'entity' => $entity,
				]);

				elgg_set_ignore_access($ia);
				return true;
		}

		return false;
	}

	/**
	 * Handle discovery
	 *
	 * @param array $segments
	 * @param string $handler
	 * @return boolean
	 */
	public static function opengraphHandler($segments, $handler) {

		switch ($segments[0]) {

			case 'edit' :
				$guid = $segments[1];
				$entity = get_entity($guid);

				if (!elgg_instanceof($entity) || !$entity->canEdit() || !is_discoverable_type($entity)) {
					return false;
				}

				$title = elgg_echo('discovery:entity:settings');
				$content = elgg_view('framework/discovery/edit', array(
					'entity' => $entity
				));
				$sidebar = false;
				$filter = false;
				break;

			case 'share' :

				$entity = null;
				$share_url = get_input('share_url');

				$guid = $segments[1];
				if ($guid) {
					$entity = get_entity($guid);
					if (!$entity) {
						return false;
					}

					if (!$share_url) {
						$share_url = $entity->getURL();
					}
					
					$entity->setVolatileData('discovery:share_url', $share_url);
				}

				$title = elgg_echo('discovery:entity:share');
				$content = elgg_view('forms/discovery/share', array(
					'entity' => $entity,
					'share_url' => $share_url,
				));

				$sidebar = false;
				$filter = false;
				break;
		}

		if ($content) {
			if (elgg_is_xhr()) {
				echo $content;
			} else {
				$layout = elgg_view_layout('content', array(
					'title' => $title,
					'content' => $content,
					'filter' => $filter,
					'sidebar' => $sidebar,
				));
				echo elgg_view_page($title, $layout);
			}
			return true;
		}
		return false;
	}

	/**
	 * Add discovery pages to public domain
	 *
	 * @param string $hook	 "public_pages"
	 * @param string $type	 "walled_garden"
	 * @param array  $return Public pages
	 * @return array
	 */
	public static function publicPages($hook, $type, $return) {
		$return[] = 'permalink/.*';
		$return[] = 'action/discovery/share';
		return $return;
	}

	/**
	 * Route old web services endpoint to the new one
	 * 
	 * @param string $hook   "route"
	 * @param string $type   "services"
	 * @param array  $return Route details
	 * @param array  $params Hook params
	 * @return array
	 */
	public static function servicesRoute($hook, $type, $return, $params) {

		if (!is_array($return)) {
			return;
		}

		$identifier = elgg_extract('identifier', $params);
		$segments = (array) elgg_extract('segments', $params, []);

		if ($identifier !== 'services') {
			return;
		}

		if (array_shift($segments) !== 'api') {
			return;
		}

		if (array_shift($segments) !== 'rest') {
			return;
		}

		if (array_shift($segments) !== 'oembed') {
			return;
		}

		$method = get_input('method');
		if ($method !== 'oembed') {
			return;
		}

		$format = get_input('format', 'json');
		$url = get_input('origin');
		$maxwidth = get_input('maxwidth');
		$maxheight = get_input('maxheight');

		$ia = elgg_set_ignore_access(true);
		$entity = get_entity_from_url(urldecode($url));
		$permalink = get_entity_permalink($entity, "{$format}+oembed");
		elgg_set_ignore_access($ia);

		if (!$permalink) {
			forward('', '404');
		}

		$permalink = elgg_http_add_url_query_elements($permalink, [
			'maxwidth' => $maxwidth,
			'maxheight' => $maxheight,
		]);

		forward($permalink);
	}

	/**
	 * Redirect to entity permalink instead of an error page if entity is discoverable
	 *
	 * @param string $hook   "forward"
	 * @param string $type   "403"|"404"
	 * @param array  $return Forward URL
	 * @param array  $params Hook params
	 * @return array
	 */
	public static function redirectErrorToPermalink($hook, $type, $return, $params) {

		$ia = elgg_set_ignore_access(true);

		$segments = _elgg_services()->request->getUrlSegments();
		$url = elgg_normalize_url(implode('/', $segments));
		$entity = get_entity_from_url($url);

		if (is_discoverable($entity)) {
			$return = get_entity_permalink($entity);
		}

		elgg_set_ignore_access($ia);

		return $return;
	}

}
