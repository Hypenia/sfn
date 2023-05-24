<?php

namespace SBW\Campaigns;

use SebastianBergmann\Money\Money;

class Forms {

	/**
	 * Setup campaign form
	 *
	 * @param string $hook   "fields"
	 * @param string $type   "campaigns/edit/about"
	 * @param array  $return Fields
	 * @param array  $params Hook params
	 *
	 * @return array
	 */
	public static function setupAboutForm($hook, $type, $return, $params) {
		$entity = elgg_extract('entity', $params);
		if (!$entity) {
			$entity = new Campaign();
		}

		$container = elgg_extract('container', $params);

		if (!$entity->started) {
			$default_access_id = $entity->guid ? $entity->future_access_id : get_default_access();
		} else {
			$default_access_id = $entity->access_id;
		}

		if ($entity->guid) {
			$default_managers = get_members_of_access_collection($entity->write_access_id, true);
		} else {
			$default_managers = [elgg_get_logged_in_user_guid()];
		}

		$default_status = $entity->isPublished() ? 'published' : 'draft';

		$fields = [
			[
				'#type' => 'hidden',
				'name' => 'guid',
				'value' => $entity->guid,
			],
			[
				'#type' => 'hidden',
				'name' => 'container_guid',
				'value' => $container->guid,
			],
			[
				'#type' => 'text',
				'#label' => elgg_echo('campaigns:field:title'),
				'name' => 'title',
				'required' => true,
				'value' => elgg_extract('title', $params, $entity->getDisplayName()),
			],
			[
				'#type' => 'text',
				'#label' => elgg_echo('campaigns:field:briefdescription'),
				'name' => 'briefdescription',
				'maxlenght' => 200,
				'required' => true,
				'value' => elgg_extract('briefdescription', $params, $entity->briefdescription),
			],
			[
				'#type' => 'campaigns/description',
				'#label' => elgg_echo('campaigns:field:description'),
				'name' => 'description',
				'required' => true,
				'value' => elgg_extract('description', $params),
				'entity' => $entity,
			],
			[
				'#type' => 'longtext',
				'#label' => elgg_echo('campaigns:field:rules'),
				'#help' => elgg_echo('campaigns:field:rules:help'),
				'name' => 'rules',
				'required' => true,
				'value' => elgg_extract('rules', $params, $entity->rules),
			],
			[
				'#type' => 'file',
				'#label' => elgg_echo('campaigns:field:icon'),
				'#help' => elgg_echo('campaigns:field:icon:help'),
				'name' => 'icon',
				'value' => $entity->guid && $entity->hasIcon('small'),
			],
			[
				'#type' => 'tags',
				'#label' => elgg_echo('tags'),
				'name' => 'tags',
				'value' => elgg_extract('tags', $params, $entity->tags),
			],
			[
				'#type' => 'url',
				'#label' => elgg_echo('campaigns:field:video_url'),
				'#help' => elgg_echo('campaigns:field:video_url:help'),
				'name' => 'video_url',
				'value' => elgg_extract('video_url', $params, $entity->video_url),
			],
			[
				'#type' => 'url',
				'#label' => elgg_echo('campaigns:field:website'),
				'name' => 'website',
				'value' => elgg_extract('website', $params, $entity->website),
			],
			[
				'#type' => 'location',
				'#label' => elgg_echo('campaigns:field:location'),
				'name' => 'location',
				'value' => elgg_extract('location', $params, $entity->location),
			],
			[
				'#type' => 'access',
				'#label' => elgg_echo('campaigns:field:access'),
				'name' => 'access_id',
				'required' => true,
				'value' => elgg_extract('access_id', $params, $default_access_id),
			],
		];

		if (!$entity->guid || $entity->owner_guid == elgg_get_logged_in_user_guid()) {
			$fields[] = [
				'#type' => 'userpicker',
				'#label' => elgg_echo('campaigns:field:managers'),
				'#help' => elgg_echo('campaigns:field:managers:help'),
				'name' => 'managers',
				'values' => elgg_extract('managers', $params, $default_managers),
			];
		}

		if ($entity->guid) {
			$target_amount = (new Money($entity->target_amount, $entity->currency))->getConvertedAmount();
			$donation_minimum = (new Money($entity->donation_minimum, $entity->currency))->getConvertedAmount();
		} else {
			$target_amount = 0;
			$donation_minimum = 0;
		}

		$non_editable = [];
		if (!$entity->started || elgg_is_admin_logged_in()) {
			// Details that can not be changed after the campaign start
			$non_editable = [
				[
					'#type' => 'fieldset',
					'#label' => elgg_echo('campaigns:field:model'),
					'class' => 'campaigns-field-model',
					'fields' => [
						[
							'#type' => 'radio',
							'#help' => elgg_echo('campaigns:model:all_or_nothing:help'),
							'name' => 'model',
							'value' => elgg_extract('model', $params, $entity->model),
							'options' => array_flip([
								Campaign::MODEL_ALL_OR_NOTHING => elgg_echo('campaigns:model:all_or_nothing'),
							]),
							'required' => true,
						],
						[
							'#type' => 'radio',
							'#help' => elgg_echo('campaigns:model:money_pot:help'),
							'name' => 'model',
							'class' => 'campaigns-field-model',
							'value' => elgg_extract('model', $params, $entity->model),
							'options' => array_flip([
								Campaign::MODEL_MONEY_POT => elgg_echo('campaigns:model:money_pot'),
							]),
							'required' => true,
						],
						[
							'#type' => 'radio',
							'#help' => elgg_echo('campaigns:model:relief:help'),
							'name' => 'model',
							'class' => 'campaigns-field-model',
							'value' => elgg_extract('model', $params, $entity->model),
							'options' => array_flip([
								Campaign::MODEL_RELIEF => elgg_echo('campaigns:model:relief'),
							]),
							'required' => true,
						],
					]
				],
				[
					'#type' => 'fieldset',
					'align' => 'horizontal',
					'fields' => [
						[
							'#type' => 'date',
							'#label' => elgg_echo('campaigns:field:calendar_start'),
							'name' => 'calendar_start',
							'timestamp' => true,
							'value' => elgg_extract('calendar_start', $params, $entity->calendar_start ?: time()),
							'class' => 'campaigns-field-calendar-start',
							'datepicker_options' => [
								'minDate' => date('Y-m-d', strtotime('+1 day')),
							],
							'required' => true,
						],
						[
							'#type' => 'date',
							'#label' => elgg_echo('campaigns:field:calendar_end'),
							'name' => 'calendar_end',
							'timestamp' => true,
							'value' => elgg_extract('calendar_end', $params, $entity->calendar_end ?: strtotime('+1 month')),
							'class' => 'campaigns-field-calendar-end',
							'datepicker_options' => [
								'minDate' => date('Y-m-d', strtotime('+1 day')),
							],
							'required' => true,
						],
					]
				],
				[
					'#type' => 'fieldset',
					'#class' => [
						'campaigns-field-monetary',
						$entity->model !== Campaign::MODEL_RELIEF ? '' : 'hidden',
					],
					'align' => 'horizontal',
					'fields' => [
						[
							'#type' => 'text',
							'#label' => elgg_echo('campaigns:field:target_amount'),
							'value' => elgg_extract('target_amount', $params, $target_amount),
							'name' => 'target_amount',
							'required' => true,
						],
						[
							'#type' => 'text',
							'#label' => elgg_echo('campaigns:field:donation_minimum'),
							'value' => elgg_extract('donation_minimum', $params, $donation_minimum),
							'name' => 'donation_minimum',
							'required' => true,
						],
						[
							'#type' => 'payments/currency',
							'#label' => elgg_echo('campaigns:field:currency'),
							'value' => elgg_extract('currency', $params, $entity->currency),
							'name' => 'currency',
							'required' => true,
						],
					],
				],
			];
		}

		$fields = array_merge($fields, $non_editable);

		$fields[] = [
			'#type' => 'plaintext',
			'#class' => [
				'campaigns-field-relief',
				$entity->model == Campaign::MODEL_RELIEF ? '' : 'hidden',
			],
			'#label' => elgg_echo('campaigns:field:relief_delivery'),
			'#help' => elgg_echo('campaigns:field:relief_delivery:help'),
			'name' => 'relief_delivery',
			'value' => elgg_extract('relief_delivery', $params, $entity->relief_delivery),
			'required' => true,
		];

		$terms = elgg_get_plugin_setting('terms:campaigner', 'sbw_campaigns');
		if ($terms) {
			$link = elgg_view('output/url', [
				'target' => '_blank',
				'href' => 'campaigns/terms/campaigner',
				'text' => elgg_echo('campaigns:terms:campaigner'),
				'class' => 'elgg-lightbox',
			]);
			$fields[] = [
				'#type' => 'checkbox',
				'name' => 'terms',
				'label' => elgg_echo('campaigns:field:terms', [$link]),
				'required' => true,
				'checked' => $entity->{'terms:campaigner'},
			];
		}

		return $fields;
	}

	/**
	 * Setup reward form
	 *
	 * @param string $hook   "fields"
	 * @param string $type   "campaigns/edit/reward"
	 * @param array  $return Fields
	 * @param array  $params Hook params
	 *
	 * @return array
	 */
	public static function setupRewardForm($hook, $type, $return, $params) {
		$entity = elgg_extract('entity', $params);
		$container = elgg_extract('container', $params);
		if (!$container instanceof Campaign) {
			return;
		}
		if (!$entity) {
			$entity = new Reward();
		}

		if ($entity->guid) {
			$donation_minimum = (new Money($entity->donation_minimum, $entity->currency))->getConvertedAmount();
		} else {
			$donation_minimum = (new Money($container->donation_minimum, $container->currency))->getConvertedAmount();
		}

		$fields = [
			[
				'#type' => 'hidden',
				'name' => 'guid',
				'value' => $entity->guid,
			],
			[
				'#type' => 'hidden',
				'name' => 'container_guid',
				'value' => $container->guid,
			],
			[
				'#type' => 'text',
				'#label' => elgg_echo('campaigns:field:title'),
				'name' => 'title',
				'required' => true,
				'value' => elgg_extract('title', $params, $entity->getDisplayName()),
			],
			[
				'#type' => 'longtext',
				'#label' => elgg_echo('campaigns:field:description'),
				'name' => 'description',
				'required' => true,
				'value' => elgg_extract('description', $params, $entity->description),
			],
			[
				'#type' => 'file',
				'#label' => elgg_echo('campaigns:field:icon'),
				'#help' => elgg_echo('campaigns:field:icon:help'),
				'name' => 'icon',
				'value' => $entity->guid && $entity->hasIcon('small'),
			],
			[
				'#type' => 'fieldset',
				'align' => 'horizontal',
				'fields' => [
					[
						'#type' => 'text',
						'#label' => elgg_echo('campaigns:field:donation_minimum'),
						'value' => elgg_extract('donation_minimum', $params, $donation_minimum),
						'name' => 'donation_minimum',
						'required' => true,
					],
					[
						'#type' => 'text',
						'#label' => elgg_echo('campaigns:field:currency'),
						'disabled' => true,
						'value' => $container->currency,
					],
					[
						'#type' => 'text',
						'#label' => elgg_echo('campaigns:field:quantity'),
						'value' => elgg_extract('quantity', $params, $entity->getStock()),
						'name' => 'quantity',
						'required' => true,
					],
				],
			],
		];

		return $fields;
	}

	/**
	 * Setup relief item form
	 *
	 * @param string $hook   "fields"
	 * @param string $type   "campaigns/edit/relief_item"
	 * @param array  $return Fields
	 * @param array  $params Hook params
	 *
	 * @return array
	 */
	public static function setupReliefItemForm($hook, $type, $return, $params) {
		$entity = elgg_extract('entity', $params);
		$container = elgg_extract('container', $params);
		if (!$container instanceof Campaign) {
			return;
		}
		if (!$entity) {
			$entity = new Reward();
		}

		$fields = [
			[
				'#type' => 'hidden',
				'name' => 'guid',
				'value' => $entity->guid,
			],
			[
				'#type' => 'hidden',
				'name' => 'container_guid',
				'value' => $container->guid,
			],
			[
				'#type' => 'text',
				'#label' => elgg_echo('campaigns:field:title'),
				'name' => 'title',
				'required' => true,
				'value' => elgg_extract('title', $params, $entity->getDisplayName()),
			],
			[
				'#type' => 'longtext',
				'#label' => elgg_echo('campaigns:field:description'),
				'name' => 'description',
				'required' => true,
				'value' => elgg_extract('description', $params, $entity->description),
			],
			[
				'#type' => 'file',
				'#label' => elgg_echo('campaigns:field:icon'),
				'name' => 'icon',
				'value' => $entity->guid && $entity->hasIcon('small'),
			],
			[
				'#type' => 'text',
				'#label' => elgg_echo('campaigns:field:required_quantity'),
				'value' => elgg_extract('required_quantity', $params, $entity->required_quantity),
				'name' => 'required_quantity',
				'required' => true,
			],
		];

		return $fields;
	}

	/**
	 * Setup news item form
	 *
	 * @param string $hook   "fields"
	 * @param string $type   "campaigns/edit/news_item"
	 * @param array  $return Fields
	 * @param array  $params Hook params
	 *
	 * @return array
	 */
	public static function setupNewsItemForm($hook, $type, $return, $params) {
		$entity = elgg_extract('entity', $params);
		$container = elgg_extract('container', $params);
		if (!$container instanceof Campaign) {
			return;
		}
		if (!$entity) {
			$entity = new NewsItem();
		}

		$fields = [
			[
				'#type' => 'hidden',
				'name' => 'guid',
				'value' => $entity->guid,
			],
			[
				'#type' => 'hidden',
				'name' => 'container_guid',
				'value' => $container->guid,
			],
			[
				'#type' => 'text',
				'#label' => elgg_echo('campaigns:field:title'),
				'name' => 'title',
				'required' => true,
				'value' => elgg_extract('title', $params, $entity->getDisplayName()),
			],
			[
				'#type' => 'longtext',
				'#label' => elgg_echo('campaigns:field:description'),
				'name' => 'description',
				'required' => true,
				'value' => elgg_extract('description', $params, $entity->description),
			],
//				[
//				'#type' => 'file',
//				'#label' => elgg_echo('campaigns:field:icon'),
//				'name' => 'icon',
//				'value' => $entity->guid && $entity->hasIcon('small'),
//			],
		];

		return $fields;
	}

}
