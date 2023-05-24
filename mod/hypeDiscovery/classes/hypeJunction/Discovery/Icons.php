<?php

namespace hypeJunction\Discovery;

use ElggIcon;

class Icons {

	/**
	 * Maps old og icons to new open graph image icons
	 *
	 * @param string $hook	'entity:icon:url'
	 * @param string $type	'site', 'object', 'user' or 'group'
	 * @param string $return Current URL
	 * @param array  $params Hook params
	 * @return string
	 */
	public static function entityIconURL($hook, $type, $return, $params) {

		$entity = elgg_extract('entity', $params);
		$size = elgg_extract('size', $params);

		$og_sizes = [
			'_og' => 'small',
			'_og_large' => 'medium',
			'_og_high' => 'large'
		];

		if (isset($og_sizes[$size])) {
			return $entity->getIconURL([
				'size' => $og_sizes[$size],
				'type' => 'open_graph_image'
			]);
		}
	}

	/**
	 * Open graph image url
	 *
	 * @param string $hook	'entity:open_graph_image:url'
	 * @param string $type	'site', 'object', 'user' or 'group'
	 * @param string $return Current URL
	 * @param array  $params Hook params
	 * @return string
	 */
	public static function entityOpenGraphImageURL($hook, $type, $return, $params) {

		$entity = elgg_extract('entity', $params);
		/* @var $entity \ElggEntity */
		$size = elgg_extract('size', $params, 'medium');

		if (!$entity->hasIcon($size, 'open_graph_image')) {
			// Default icons are smaller
			$sizes = [
				'small' => 'medium',
				'medium' => 'large',
				'large' => 'master',
			];
			if ($entity->hasIcon($sizes[$size])) {
				$icon = $entity->getIcon($sizes[$size]);
			} else {
				$icon = elgg_get_site_entity()->getIcon($size, 'open_graph_image');
			}
		} else {
			$icon = $entity->getIcon($size, 'open_graph_image');
		}
		
		return elgg_get_inline_url($icon, false);
	}

	/**
	 * Configure open graph image sizes
	 *
	 * @param string $hook   "entity:open_graph_image:sizes"
	 * @param string $type   "object", "user", "group", "site"
	 * @param array  $return Sizes
	 * @param array  $params Hook params
	 * @return array
	 */
	public static function entityOpenGraphImageSizes($hook, $type, $return, $params) {
		$sizes = [
			'large' => [
				'w' => 1200,
				'h' => 1200,
				'square' => false,
				'upscale' => false,
			],
			'medium' => [
				'w' => 600,
				'h' => 600,
				'square' => false,
				'upscale' => false,
			],
			'small' => [
				'w' => 200,
				'h' => 200,
				'square' => false,
				'upscale' => true,
			],
			'original' => [],
		];
		return array_merge($return, $sizes);
	}

	/**
	 * Configure open graph image file
	 *
	 * @param string   $hook   "entity:open_graph_image:file"
	 * @param string   $type   "object", "user", "group", "site"
	 * @param ElggIcon $return File
	 * @param array    $params Hook params
	 * @return ElggIcon
	 */
	public static function entityOpenGraphImageFile($hook, $type, $return, $params) {

		// mapping to old size config
		$og_sizes = [
			'small' => '_og',
			'medium' => '_og_large',
			'large' => '_og_high',
		];

		$entity = elgg_extract('entity', $params);
		$size = elgg_extract('size', $params, 'medium');

		$size = elgg_extract($size, $og_sizes, $size);

		$return->owner_guid = $entity->owner_guid ? : $entity->guid;
		$return->setFilename("og_image/$entity->guid/$size.jpg");

		return $return;
	}

}
