<?php

namespace hypeJunction\Discovery;

class Discovery {

	/**
	 * Prepare alternate link tags
	 *
	 * @param string $hook   "head"
	 * @param string $type   "page"
	 * @param array  $return Tags
	 * @param array  $params Hook params
	 * @return array
	 */
	public static function prepareAlternateLinks($hook, $type, $return, $params) {

		$title = elgg_extract('title', $params);

		$ia = elgg_set_ignore_access(true);

		$segments = _elgg_services()->request->getUrlSegments();
		$url = elgg_normalize_url(implode('/', $segments));
		$entity = get_entity_from_url($url);

		if (is_embeddable($entity)) {
			$return['links']['json+oembed'] = array(
				'rel' => 'alternate',
				'type' => 'application/json+oembed',
				'href' => get_entity_permalink($entity, 'json+oembed'),
				'title' => $title,
			);

			if (elgg_is_active_plugin('data_views')) {
				$return['links']['xml+oembed'] = array(
					'rel' => 'alternate',
					'type' => 'application/xml+oembed',
					'href' => get_entity_permalink($entity, 'xml+oembed'),
					'title' => $title,
				);
			}
		}

		elgg_set_ignore_access($ia);

		return $return;
	}

	/**
	 * Prepare open graph and other discovery tags
	 *
	 * @param string $hook   "head"
	 * @param string $type   "page"
	 * @param array  $return Tags
	 * @param array  $params Hook params
	 * @return array
	 */
	public static function prepareMetas($hook, $type, $return, $params) {

		$segments = _elgg_services()->request->getUrlSegments();
		$url = elgg_normalize_url(implode('/', $segments));
		$metatags = get_discovery_metatags($url);

		if (empty($metatags)) {
			return;
		}

		if (!isset($metatags['og:title']) && isset($params['title'])) {
			$metatags['og:title'] = $params['title'];
		}

		if (!empty($metatags) && is_array($metatags)) {
			foreach ($metatags as $name => $content) {
				if (!$content) {
					continue;
				}
				$name_parts = explode(':', $name);
				$namespace = array_shift($name_parts);

				$ogp = array('og', 'fb', 'article', 'profile', 'book', 'music', 'video', 'profile', 'website');
				if (in_array($namespace, $ogp)) {
					// OGP tags use 'property=""' attribute
					$return['metas'][$name] = [
						'property' => $name,
						'content' => $content,
					];
				} else {
					$return['metas'][$name] = [
						'name' => $name,
						'content' => $content,
					];
				}
			}
		}

		return $return;
	}

	/**
	 * Get exportable representation of an entity for oEmbed
	 *
	 * @param string $hook   "export:entity"
	 * @param string $type   "oembed"
	 * @param array  $return Current exportable values
	 * @param array  $params Hooks params
	 * @return array
	 */
	public static function oEmbedExport($hook, $type, $return, $params) {

		$entity = elgg_extract('entity', $params);
		$maxwidth = elgg_extract('maxwidth', $params);
		$maxheight = elgg_extract('maxheight', $params);
		$height = $maxheight ? : 480;
		$width = $maxwidth ? : 640;

		if (!is_embeddable($entity)) {
			return $return;
		}

		$return['type'] = 'rich';
		$return['thumbnail_url'] = $entity->getIconURL([
			'size' => 'medium',
			'type' => 'open_graph_image',
		]);

		$iframe_attrs = elgg_format_attributes(array(
			'src' => get_entity_permalink($entity, 'oembed'),
			'frameborder' => 0,
			'height' => $height,
			'width' => $width,
			'scrolling' => 'auto',
			'seamless' => true,
		));

		$return['html'] = "<iframe $iframe_attrs></iframe>";
		$return['width'] = $width;
		$return['height'] = $height;

		return $return;
	}

	/**
	 * Header metatags
	 *
	 * @param string $hook   "metatags"
	 * @param string $type   "discovery"
	 * @param array  $return Metatags
	 * @param array  $params Hook params
	 * @return array
	 */
	public static function graphExport($hook, $type, $return, $params) {

		$entity = elgg_extract('entity', $params);
		$url = elgg_extract('url', $params);

		$site = elgg_get_site_entity();
		$site_tags = array(
			'og:type' => 'website',
			'og:site_name' => $site->og_site_name,
			'og:image' => get_discovery_image_url($site),
			'og:url' => $url,
			'og:description' => get_discovery_description($site),
			'fb:app_id' => $site->fb_app_id,
			'twitter:card' => 'summary',
			'twitter:site' => $site->twitter_site,
		);

		$return = array_merge($return, $site_tags);

		if (!is_discoverable($entity)) {
			return $return;
		}

		$type = $entity->getType();
		$subtype = $entity->getSubtype();

		$image_url = get_discovery_image_url($entity);
		if (file_exists($image_url)) {
			$image_size = getimagesize($image_url);
			$image_width = $image_size[0];
			$image_height = $image_size[1];
		}

		switch ($type) {

			default :
			case 'object' :
				$owner = $entity->getOwnerEntity();
				$entity_tags = array(
					'og:type' => 'article',
					'og:title' => get_discovery_title($entity),
					'og:image' => $image_url,
					'og:image:width' => $image_width,
					'og:image:height' => $image_height,
					'og:url' => get_entity_permalink($entity),
					'og:description' => get_discovery_description($entity),
					'article:published_time' => date("Y-m-d", $entity->time_created),
					'article:author' => ($owner) ? $owner->getURL() : '',
					'article:tags' => get_discovery_keywords($entity),
					'twitter:creator' => ($owner) ? $owner->twitter : '',
				);
				break;

			case 'user' :
				$entity_tags = array(
					'og:type' => 'profile',
					'og:title' => get_discovery_title($entity),
					'og:image' => $image_url,
					'og:image:width' => $image_width,
					'og:image:height' => $image_height,
					'og:url' => get_entity_permalink($entity),
					'og:description' => get_discovery_description($entity),
					'profile:username' => $entity->username,
					'twitter:creator' => $entity->twitter,
				);
				break;

			case 'site' :
				$entity_tags = array();
				break;
		}

		$return = array_merge($return, $entity_tags);

		return $return;
	}

}
