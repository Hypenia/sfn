<?php

/**
 * Crowdfunding campaigns
 * 
 * @author Ismayil Khayredinov <info@hypejunction.com>
 * @copyright Copyright (c) 2016, Ismayil Khayredinov
 * @copyright Copyright (c) 2016, Social Business World
 */
require_once __DIR__ . '/autoloader.php';

use Elgg\Values;
use SBW\Campaigns\Campaign;
use SBW\Campaigns\Commitments;
use SBW\Campaigns\Cron;
use SBW\Campaigns\Donation;
use SBW\Campaigns\Forms;
use SBW\Campaigns\Icons;
use SBW\Campaigns\Maps;
use SBW\Campaigns\Menus;
use SBW\Campaigns\NewsItem;
use SBW\Campaigns\Notifications;
use SBW\Campaigns\Payments;
use SBW\Campaigns\Permissions;
use SBW\Campaigns\Router;

elgg_register_event_handler('init', 'system', function() {

	$subtype = Campaign::SUBTYPE;

	// Routes and URLs
	elgg_register_page_handler('campaigns', [Router::class, 'controller']);
	elgg_register_plugin_hook_handler('entity:url', 'object', [Router::class, 'urlHandler']);

	// Permissions
	elgg_register_plugin_hook_handler('permissions_check', 'object', [Permissions::class, 'canEdit']);
	elgg_register_plugin_hook_handler('permissions_check:delete', 'object', [Permissions::class, 'canDelete']);
	elgg_register_plugin_hook_handler('access:collections:write', 'user', [Permissions::class, 'setupWriteAccess']);
	elgg_register_event_handler('update:after', 'object', [Permissions::class, 'syncAccess']);

	// Forms/actions
	elgg_register_plugin_hook_handler('fields', 'campaigns/edit/about', [Forms::class, 'setupAboutForm']);
	elgg_register_action('campaigns/edit/about', __DIR__ . '/actions/campaigns/edit/about.php');

	elgg_register_plugin_hook_handler('fields', 'campaigns/edit/reward', [Forms::class, 'setupRewardForm']);
	elgg_register_action('campaigns/edit/reward', __DIR__ . '/actions/campaigns/edit/reward.php');

	elgg_register_plugin_hook_handler('fields', 'campaigns/edit/relief_item', [Forms::class, 'setupReliefItemForm']);
	elgg_register_action('campaigns/edit/relief_item', __DIR__ . '/actions/campaigns/edit/relief_item.php');

	elgg_register_plugin_hook_handler('fields', 'campaigns/edit/news_item', [Forms::class, 'setupNewsItemForm']);
	elgg_register_action('campaigns/edit/news_item', __DIR__ . '/actions/campaigns/edit/news_item.php');

	elgg_register_action('campaigns/edit/payout', __DIR__ . '/actions/campaigns/edit/payout.php');

	elgg_register_action('campaigns/verify', __DIR__ . '/actions/campaigns/verify.php', 'admin');
	elgg_register_action('campaigns/publish', __DIR__ . '/actions/campaigns/publish.php');

	elgg_register_action('campaigns/give', __DIR__ . '/actions/campaigns/give.php', 'public');
	elgg_register_action('campaigns/cancel', __DIR__ . '/actions/campaigns/cancel.php', 'public');

	elgg_register_action('campaigns/start', __DIR__ . '/actions/campaigns/start.php', 'admin');
	elgg_register_action('campaigns/end', __DIR__ . '/actions/campaigns/end.php', 'admin');

	elgg_register_action('campaigns/follow', __DIR__ . '/actions/campaigns/follow.php');
	elgg_register_action('campaigns/unfollow', __DIR__ . '/actions/campaigns/unfollow.php');

	elgg_register_action('campaigns/checkout', __DIR__ . '/actions/campaigns/checkout.php', 'public');

	elgg_register_action('campaigns/is_registered', __DIR__ . '/actions/campaigns/is_registered.php', 'public');
	
	// Menus
	elgg_register_menu_item('site', [
		'name' => 'campaigns',
		'href' => 'campaigns',
		'text' => elgg_echo('campaigns'),
	]);

	elgg_register_plugin_hook_handler('register', 'menu:filter', [Menus::class, 'setupEditFilterMenu']);
	elgg_register_plugin_hook_handler('register', 'menu:entity', [Menus::class, 'setupCampaignEntityMenu']);
	elgg_register_plugin_hook_handler('register', 'menu:entity', [Menus::class, 'setupRewardEntityMenu']);
	elgg_register_plugin_hook_handler('register', 'menu:entity', [Menus::class, 'setupReliefItemEntityMenu']);
	elgg_register_plugin_hook_handler('register', 'menu:entity', [Menus::class, 'setupNewsEntityMenu']);
	elgg_register_plugin_hook_handler('register', 'menu:owner_block', [Menus::class, 'setupOwnerBlockMenu']);

	// Assets
	elgg_extend_view('elgg.css', 'campaigns/stylesheet.css');

	// Icons
	elgg_register_plugin_hook_handler('entity:icon:sizes', 'object', [Icons::class, 'configureIconSizes']);

	// Payments
	elgg_register_plugin_hook_handler('payment_methods', 'campaigns', [Payments::class, 'getPaymentMethods']);
	elgg_register_plugin_hook_handler('charges', 'campaigns', [Payments::class, 'getCharges']);
	elgg_register_plugin_hook_handler('transaction:paid', 'payments', [Payments::class, 'processPaidTransaction']);
	elgg_register_plugin_hook_handler('transaction:refunded', 'payments', [Payments::class, 'processRefundedTransaction']);
	elgg_register_plugin_hook_handler('transaction:failed', 'payments', [Payments::class, 'processFailedTransaction']);
	elgg_register_event_handler('end', 'object', [Payments::class, 'endCampaign']);

	// @todo: add a hook handler
	elgg_register_plugin_hook_handler('transaction:partially_refunded', 'payments', [Payments::class, 'processPartiallyRefundedTransaction']);

	// Relief commitments
	elgg_register_plugin_hook_handler('transaction:committed', 'payments', [Commitments::class, 'processCommitment']);
	elgg_register_plugin_hook_handler('transaction:confirmed', 'payments', [Commitments::class, 'processConfirmation']);
	elgg_register_plugin_hook_handler('transaction:received', 'payments', [Commitments::class, 'processDelivery']);
	elgg_register_plugin_hook_handler('register', 'menu:annotation', [Menus::class, 'setupCommittedAnnotationMenu']);
	elgg_register_action('campaigns/commitment/change_status', __DIR__ . '/actions/campaigns/commitment/change_status.php');
	elgg_register_action('campaigns/commitment/delete', __DIR__ . '/actions/campaigns/commitment/delete.php', 'admin');


	// Notifications
	elgg_register_notification_event('object', $subtype, ['start', 'milestone', 'end']);
	elgg_register_plugin_hook_handler('format', 'notification:start:object:campaign', [Notifications::class, 'formatStartNotification']);
	elgg_register_plugin_hook_handler('format', 'notification:milestone:object:campaign', [Notifications::class, 'formatMilestoneNotification']);
	elgg_register_plugin_hook_handler('format', 'notification:end:object:campaign', [Notifications::class, 'formatEndNotification']);

	elgg_register_notification_event('object', Donation::SUBTYPE, ['create']);
	elgg_register_plugin_hook_handler('format', 'notification:create:object:campaign_donation', [Notifications::class, 'formatDonationNotification']);

	elgg_register_notification_event('object', NewsItem::SUBTYPE, ['create']);
	elgg_register_plugin_hook_handler('format', 'notification:create:object:campaign_news', [Notifications::class, 'formatNewsNotification']);

	elgg_register_plugin_hook_handler('get', 'subscriptions', [Notifications::class, 'getSubscriptions']);
	
	// Cron jobs
	elgg_register_plugin_hook_handler('cron', 'hourly', [Cron::class, 'endCampaigns']);
	elgg_register_plugin_hook_handler('cron', 'hourly', [Cron::class, 'startCampagns']);
	elgg_register_plugin_hook_handler('cron', 'daily', [Cron::class, 'sendPaymentReminders']);

	// Search
	elgg_register_entity_type('object', $subtype);

	// Likes
	elgg_register_plugin_hook_handler('likes:is_likable', "object:$subtype", [Values::class, 'getTrue']);

	elgg_register_admin_menu_item('administer', 'campaign_balances', 'payments');

	// Maps
	elgg_register_plugin_hook_handler('marker', 'object', [Maps::class, 'setCampaignMarker']);
	
});

elgg_register_event_handler('upgrade', 'system', function() {
	require_once __DIR__ . '/activate.php';
	require_once __DIR__ . '/lib/upgrades.php';
});
