<?php

namespace SBW\Campaigns;

class Router {

	/**
	 * /campaigns page handler
	 *
	 * @param array $segments URL segments
	 * @return bool
	 */
	public static function controller($segments) {

		$page = array_shift($segments);

		switch ($page) {

			default :
			case 'active' :
				$resource = elgg_view_resource('campaigns/active');
				break;
			
			case 'all' :
				$resource = elgg_view_resource('campaigns/all');
				break;

			case 'map' :
				$resource = elgg_view_resource('campaigns/map');
				break;

			case 'owner' :
				$resource = elgg_view_resource('campaigns/owner', [
					'username' => array_shift($segments),
				]);
				break;

			case 'friends' :
				$resource = elgg_view_resource('campaigns/friends', [
					'username' => array_shift($segments),
				]);
				break;

			case 'group' :
				$resource = elgg_view_resource('campaigns/group', [
					'guid' => array_shift($segments),
				]);
				break;

			case 'add' :
				$resource = elgg_view_resource('campaigns/add', [
					'container_guid' => array_shift($segments),
				]);
				break;

			case 'edit' :
				$resource = elgg_view_resource('campaigns/edit', [
					'guid' => array_shift($segments),
					'filter_context' => array_shift($segments) ?: 'about',
				]);
				break;

			case 'view' :
				$resource = elgg_view_resource('campaigns/view', [
					'guid' => array_shift($segments),
					'filter_context' => array_shift($segments) ?: 'about',
				]);
				break;

			case 'give' :
				$resource = elgg_view_resource('campaigns/give', [
					'guid' => array_shift($segments),
				]);
				break;

			case 'checkout' :
				$resource = elgg_view_resource('campaigns/checkout', [
					'guid' => array_shift($segments),
				]);
				break;

			case 'commitment' :
				$resource = elgg_view_resource('campaigns/commitment', [
					'guid' => array_shift($segments),
					'filter_context' => array_shift($segments),
				]);
				break;

			case 'thankyou' :
				$resource = elgg_view_resource('campaigns/thankyou', [
					'guid' => array_shift($segments),
				]);
				break;

			case 'terms' :
				$resource = elgg_view_resource('campaigns/terms', [
					'subject' => array_shift($segments) ?: 'campaigner',
				]);
				break;

			case 'news' :
				$resource = elgg_view_resource('campaigns/news', [
					'guid' => array_shift($segments),
				]);
				break;

			case 'download' :
				$guid = array_shift($segments);
				$report = array_shift($segments);
				switch ($report) {
					case 'transactions':
					case 'commitments' :
						$resource = elgg_view_resource("campaigns/download/$report", [
							'guid' => $guid,
						]);
						break;
				}
				break;

			case 'confirm' :
				$resource = elgg_view_resource('campaigns/confirm');
				break;
		}

		if ($resource) {
			return elgg_ok_response($resource);
		}

		return elgg_error_response('', REFERRER, ELGG_HTTP_NOT_FOUND);
	}

	/**
	 * Handle campaign URL
	 * 
	 * @param string $hook   "entity:url"
	 * @param string $type   "object"
	 * @param string $return URL
	 * @param array  $params Hook params
	 * @return string
	 */
	public static function urlHandler($hook, $type, $return, $params) {

		$entity = elgg_extract('entity', $params);
		if ($entity instanceof Campaign) {
			$friendly = elgg_get_friendly_title($entity->getDisplayName());
			return elgg_normalize_url("campaigns/view/$entity->guid/about/$friendly");
		}

		if ($entity instanceof NewsItem) {
			$friendly = elgg_get_friendly_title($entity->getDisplayName());
			return elgg_normalize_url("campaigns/news/$entity->guid/$friendly");
		}

		if ($entity instanceof Commitment) {
			return elgg_normalize_url("campaigns/commitment/$entity->guid");
		}
	}

}
