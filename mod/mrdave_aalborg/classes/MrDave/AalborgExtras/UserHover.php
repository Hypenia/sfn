<?php
namespace MrDave\AalborgExtras;

use UFCOE\Elgg\MenuList;

class UserHover {
	static public function prepareMenu($h, $t, $v, $p) {
		if (!elgg_in_context('mrdave_aalborg_topbar')) {
			return;
		}
		$action_section = new MenuList(elgg_extract('action', $v, []));

		$action_section->remove('avatar:edit');

		$user = $p['entity'];
		/* @var \ElggUser $user */

		if (elgg_is_active_plugin('notifications')) {
			$item = \ElggMenuItem::factory(array(
				'name' => '2_a_user_notify',
				'text' => elgg_echo('notifications:subscriptions:changesettings'),
				'href' => "notifications/personal/{$user->username}",
				'section' => "notifications",
			));
			$action_section->push($item);

			if (elgg_is_active_plugin('groups')) {
				$item = \ElggMenuItem::factory(array(
					'name' => '2_group_notify',
					'text' => elgg_echo('notifications:subscriptions:changesettings:groups'),
					'href' => "notifications/group/{$user->username}",
					'section' => "notifications",
				));
				$action_section->push($item);
			}
		}

		$item = \ElggMenuItem::factory(array(
			'name' => 'logout',
			'text' => elgg_view_icon('sign-out') . elgg_echo('logout'),
			'href' => elgg_add_action_tokens_to_url("action/logout"),
		));
		$action_section->push($item);

		$v['action'] = $action_section->getItems();
		return $v;
	}
}
