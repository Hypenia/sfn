<?php

/**
 * This file contains all supportive functions for the Newsletter plugin
 */

/**
 * Start the commandline to send a newsletter
 * This is offloaded because it could take a while and/or resources
 *
 * @param Newsletter $entity Newsletter entity to be processed
 *
 * @return void
 */
function newsletter_start_commandline_sending(Newsletter $entity) {
	
	if (!elgg_instanceof($entity, 'object', Newsletter::SUBTYPE)) {
		return;
	}
	
	// prepare commandline settings
	$settings = [
		'entity_guid' => $entity->getGUID(),
		'host' => $_SERVER['HTTP_HOST'],
		'memory_limit' => ini_get('memory_limit'),
		'secret' => newsletter_generate_commanline_secret($entity->getGUID()),
	];
	if (isset($_SERVER['HTTPS'])) {
		$settings['https'] = $_SERVER['HTTPS'];
	}
	
	// ini settings
	$ini_param = '';
	$ini_file = php_ini_loaded_file();
	if (!empty($ini_file)) {
		$ini_param = "-c {$ini_file} ";
	}
	
	// which script to run
	$script_location = dirname(dirname(__FILE__)) . '/procedures/cli.php';
	
	// convert settings to commandline params
	$query_string = http_build_query($settings, '', ' ');
	
	// start the correct commandline
	if (PHP_OS === 'WINNT') {
		pclose(popen('start /B php ' . $ini_param . $script_location . ' ' . $query_string, 'r'));
	} else {
		exec('php ' . $ini_param . $script_location . ' ' . $query_string . ' > /dev/null &');
	}
}

/**
 * Generate a secret to be used for validation
 *
 * @param int $entity_guid guid of the newsletter
 *
 * @return boolean|string
 */
function newsletter_generate_commanline_secret($entity_guid) {
	
	$entity_guid = sanitise_int($entity_guid, false);
	if (empty($entity_guid)) {
		return false;
	}
	
	$plugin = elgg_get_plugin_from_id('newsletter');
	$hmac = elgg_build_hmac([$plugin->getGUID(), $entity_guid, $plugin->time_created]);
	return $hmac->getToken();
}

/**
 * Validate if the provided secret is correct
 *
 * @param int    $entity_guid guid of the newsletter entity
 * @param string $secret      secret code to be validated
 *
 * @return boolean
 */
function newsletter_validate_commandline_secret($entity_guid, $secret) {
	
	$entity_guid = sanitise_int($entity_guid, false);
	if (empty($entity_guid) || empty($secret)) {
		return false;
	}
	
	$correct_secret = newsletter_generate_commanline_secret($entity_guid);
	
	if (!empty($correct_secret) && ($correct_secret === $secret)) {
		return true;
	}
	
	return false;
}

/**
 * Process the newsletter for a given guid
 *
 * @param int $entity_guid guid of the newsletter
 *
 * @return void
 */
function newsletter_process($entity_guid) {
	
	$entity_guid = sanitise_int($entity_guid, false);
	if (empty($entity_guid)) {
		return;
	}
	
	// ignore access
	$ia = elgg_set_ignore_access(true);
	
	$entity = get_entity($entity_guid);
	// is this a Newsletter
	if (!elgg_instanceof($entity, 'object', Newsletter::SUBTYPE)) {
		elgg_set_ignore_access($ia);
		return;
	}
	
	$logging = ['start_time' => time()];
	
	$site = elgg_get_site_entity();
	$container = $entity->getContainerEntity();
	$dbprefix = elgg_get_config('dbprefix');
	
	// set newsletter status to sending
	$entity->status = 'sending';
	$entity->start_time = $logging['start_time'];
	
	// get the recipients
	
	// basic set of user selection options
	$basic_user_options = [
		'type' => 'user',
		'limit' => false,
		'selects' => ['ue.email'],
		'joins' => ['JOIN ' . $dbprefix . 'users_entity ue ON e.guid = ue.guid'],
		'callback' => 'newsletter_user_row_to_subscriber_info',
	];
	// include users without settings
	if (newsletter_include_existing_users()) {
		// yes, so exclude blocked
		$basic_user_options['wheres'] = [
			"(e.guid NOT IN (SELECT guid_one
				FROM " . $dbprefix . "entity_relationships
				WHERE relationship = '" . NewsletterSubscription::GENERAL_BLACKLIST . "'
				AND guid_two = " . $site->getGUID() . ")
			)",
			"(e.guid NOT IN (SELECT guid_one
				FROM " . $dbprefix . "entity_relationships
				WHERE relationship = '" . NewsletterSubscription::BLACKLIST . "'
				AND guid_two = " . $container->getGUID() . ")
			)",
		];
	} else {
		// no, so subscription is required
		$basic_user_options['wheres'] = [
			"(e.guid IN (SELECT guid_one
				FROM " . $dbprefix . "entity_relationships
				WHERE relationship = '" . NewsletterSubscription::SUBSCRIPTION . "'
				AND guid_two = " . $container->getGUID() . ")
			)",
		];
	}
	
	$filtered_recipients = ['users' => [],	'emails' => []];
	$recipients = $entity->getRecipients();
	if (empty($recipients)) {
		// no recipients so report error
		$entity->status = 'sent';
		
		return false;
	}
	
	// recipients is an array consisting of:
	// - user_guids: 	individual users
	// - group_guids:	groups to send the content to
	// - emails:		individual email addresses
	// - subscribers:	(int) whether or not to add the subscribers of the container
	// - members:		(int) whether or not to aad the member of the container
	$user_guids = elgg_extract('user_guids', $recipients);
	if (!empty($user_guids)) {
		if (!is_array($user_guids)) {
			$user_guids = [$user_guids];
		}
		
		// convert to a format we can use
		$options = $basic_user_options;
		$options['wheres'][] = '(e.guid IN (' . implode(',', $user_guids) . '))';
		
		$users = elgg_get_entities($options);
		if (!empty($users)) {
			$new_users = [];
			foreach ($users as $user) {
				$new_users[$user['guid']] = $user['email'];
			}
			$filtered_recipients['users'] += $new_users;
		}
	}
	
	$group_guids = elgg_extract('group_guids', $recipients);
	if (!empty($group_guids)) {
		if (!is_array($group_guids)) {
			$group_guids = [$group_guids];
		}
		
		$options = $basic_user_options;
		$options['joins'][] = 'JOIN ' . $dbprefix . 'entity_relationships r ON e.guid = r.guid_one';
		$options['wheres'][] = '(r.guid_two IN (' . implode(',', $group_guids) . ') AND r.relationship = "member")';
		
		$users = elgg_get_entities($options);
		if (!empty($users)) {
			$new_users = [];
			foreach ($users as $user) {
				$new_users[$user['guid']] = $user['email'];
			}
			$filtered_recipients['users'] += $new_users;
		}
	}
	
	$subscribers = elgg_extract('subscribers', $recipients);
	if (!empty($subscribers)) {
		$subscribers = newsletter_get_subscribers($container);
		
		$filtered_recipients['users'] += $subscribers['users'];
		$filtered_recipients['emails'] = array_merge($filtered_recipients['emails'], $subscribers['emails']);
	}
	
	$members = elgg_extract('members', $recipients);
	if (!empty($members)) {
		$relationship = 'member';
		if (elgg_instanceof($container, 'site')) {
			$relationship = 'member_of_site';
		}
		
		$options = $basic_user_options;
		$options['relationship'] = $relationship;
		$options['relationship_guid'] = $container->getGUID();
		$options['inverse_relationship'] = true;
		
		$users = elgg_get_entities_from_relationship($options);
		if (!empty($users)) {
			$new_users = [];
			foreach ($users as $user) {
				$new_users[$user['guid']] = $user['email'];
			}
			$filtered_recipients['users'] += $new_users;
		}
	}
	
	$emails = elgg_extract('emails', $recipients);
	if (!empty($emails)) {
		if (!is_array($emails)) {
			$emails = [$emails];
		}
		
		// get blocked users
		$options = [
			'type' => 'user',
			'limit' => false,
			'selects' => ['ue.email'],
			'joins' => ['JOIN ' . $dbprefix . 'users_entity ue ON e.guid = ue.guid'],
			'wheres' => [
				"(ue.email IN ('" . implode("','", $emails) . "'))",
				"(e.guid IN (SELECT guid_one
					FROM " . $dbprefix . "entity_relationships
					WHERE relationship = '" . NewsletterSubscription::GENERAL_BLACKLIST . "'
					AND guid_two = " . $site->getGUID() . ")
				OR
				e.guid IN (SELECT guid_one
					FROM " . $dbprefix . "entity_relationships
					WHERE relationship = '" . NewsletterSubscription::BLACKLIST . "'
					AND guid_two = " . $container->getGUID() . ")
				)",
			],
			'callback' => 'newsletter_user_row_to_subscriber_info',
		];
		
		$users = elgg_get_entities($options);
		if (!empty($users)) {
			$blocked_emails = [];
			foreach ($users as $user) {
				$blocked_emails[] = $user['email'];
			}
			
			$emails = array_diff($emails, $blocked_emails);
		}
		
		if (!empty($emails)) {
			// get blocked emails
			$options = [
				'type' => 'object',
				'subtype' => NewsletterSubscription::SUBTYPE,
				'limit' => false,
				'selects' => ['oe.title AS email'],
				'joins' => ['JOIN ' . $dbprefix . 'objects_entity oe ON e.guid = oe.guid'],
				'wheres' => [
					"(oe.title IN ('" . implode("','", $emails) . "'))",
					"(e.guid IN (SELECT guid_one
						FROM " . $dbprefix . "entity_relationships
						WHERE relationship = '" . NewsletterSubscription::GENERAL_BLACKLIST . "'
						AND guid_two = " . $site->getGUID() . ")
					OR
					e.guid IN (SELECT guid_one
						FROM " . $dbprefix . "entity_relationships
						WHERE relationship = '" . NewsletterSubscription::BLACKLIST . "'
						AND guid_two = " . $container->getGUID() . ")
					)"
				],
				'callback' => 'newsletter_user_row_to_subscriber_info',
			];
			
			$subscriptions = elgg_get_entities($options);
			if (!empty($subscriptions)) {
				$blocked_emails = [];
				foreach ($subscriptions as $subscription) {
					$blocked_emails[] = $subscription['email'];
				}
				
				$emails = array_diff($emails, $blocked_emails);
			}
			
			if (!empty($emails)) {
				$filtered_recipients['emails'] = array_merge($filtered_recipients['emails'], $emails);
			}
		}
	}
	
	// get newsletter content
	if ($entity->subject) {
		$message_subject = $entity->subject;
	} else {
		$message_subject = elgg_echo('newsletter:subject', [$container->name, $entity->title]);
	}
	$message_plaintext_content = elgg_echo('newsletter:plain_message', [elgg_normalize_url($entity->getURL())]);
	
	$message_html_content = elgg_view_layout('newsletter', ['entity' => $entity]);
	// convert to inline CSS for email clients
	$message_html_content = html_email_handler_css_inliner($message_html_content);
	
	// proccess all recipients
	if (newsletter_custom_from_enabled() && !empty($entity->from)) {
		// from is validated to a valid email address in the newsletter save action
		$from = $entity->from;
	} else {
		// default to the container email address
		$from = html_email_handler_make_rfc822_address($container);
	}
	
	// set default send options
	$send_options = [
		'from' => $from,
		'subject' => $message_subject,
		'plaintext_message' => $message_plaintext_content,
	];
	
	foreach ($filtered_recipients as $type => $recipients) {
		
		if (empty($recipients)) {
			continue;
		}
		
		foreach ($recipients as $id => $recipient) {
			$recipient_log = [
				'type' => $type,
				'email' => $recipient,
				'time' => date(DATE_RFC1123),
				'timestamp' => time(),
				'status' => false,
			];
			
			// create individual footer for unsubscribe link
			if ($type == 'users') {
				$recipient_log['guid'] = $id;
				
				$unsubscribe_link = newsletter_generate_unsubscribe_link($container, $id);
			} else {
				$unsubscribe_link = newsletter_generate_unsubscribe_link($container, $recipient);
			}
			
			// place the unsubscribe link in the message
			$unsubscribe_link = elgg_normalize_url($unsubscribe_link);
			$message_html_content_user = str_ireplace(urlencode("{unsublink}"), $unsubscribe_link, $message_html_content);
			
			// replace the online link for logged out users to add an emailadres
			if ($type !== 'users') {
				$online_link = $entity->getURL();
				$new_online_link = $online_link . '?e=' . $recipient;
				
				$message_html_content_user = str_ireplace($online_link, $new_online_link, $message_html_content_user);
			}
			
			// add URL postfix to all internal links
			$message_html_content_user = newsletter_apply_url_postfix($message_html_content_user, $entity);
			
			// send mail
			$send_options['to'] = $recipient;
			$send_options['html_message'] = $message_html_content_user;
			
			$recipient_log['status'] = html_email_handler_send_email($send_options);
			
			if ($recipient_log['status'] && !empty($recipient_log['guid'])) {
				$entity->addRelationship($recipient_log['guid'], Newsletter::SEND_TO);
			}
			
			// add to logging
			$logging['recipients'][] = $recipient_log;
			
			$entity->saveLogging($logging);
		}
	}
	
	$logging['end_time'] = time();
	
	$entity->saveLogging($logging);
	
	// set newsletter status to done
	$entity->status = 'sent';
	
	// needed to trigger the update ts so we now something changed
	$entity->save();
	
	// send status notification
	if (newsletter_is_email_address($entity->status_notification)) {
		$from = html_email_handler_make_rfc822_address($site);
		$subject = elgg_echo('newsletter:status_notification:subject');
		$message = elgg_echo('newsletter:status_notification:message', [$entity->title, $entity->getURL()]);
		
		elgg_send_email($from, $entity->status_notification, $subject, $message);
	}
	
	// restore access
	elgg_set_ignore_access($ia);
}

/**
 * Format a recipient to be listed in the recipient autocomplete or listing
 *
 * @param mixed $recipient Can be an ElggUser, ElggGroup or email address
 *
 * @return array Contains, of false on failure
 * 					- type : user, group or email
 * 					- label: used in the autocomplete dropdown
 * 					- content: used in the listing view
 * 					- value: eighter a guid of email address
 */
function newsletter_format_recipient($recipient) {
	
	if (empty($recipient)) {
		return false;
	}
	
	if (elgg_instanceof($recipient, 'user')) {
		$icon = elgg_view_entity_icon($recipient, 'tiny', [
			'use_hover' => false,
			'use_link' => false,
		]);
		
		// label for autoselect listing
		$label = elgg_view_image_block($icon, $recipient->name, ['class' => 'elgg-autocomplete-item']);
		
		// content for if selected
		$content = elgg_view('input/hidden', ['name' => 'user_guids[]', 'value' => $recipient->getGUID()]);
		$content .= elgg_view_image_block($icon, $recipient->name, ['image_alt' => elgg_view_icon('delete-alt')]);
		
		return [
			'type' => 'user',
			'value' => $recipient->getGUID(),
			'label' => $label,
			'content' => elgg_format_element('div', ['class' => 'newsletter-recipient-result phs'], $content),
		];
	} elseif (elgg_instanceof($recipient, 'group')) {
		$icon = elgg_view_entity_icon($recipient, 'tiny', [
			'use_hover' => false,
			'href' => false,
		]);
		
		// label for autoselect listing
		$name = elgg_format_element('span', [], elgg_echo('groups:group') . ': ') . $recipient->name;
		$name .= elgg_format_element('span', ['class' => 'elgg-quiet'], ' (' . $recipient->getMembers(0, 0, true) . ' ' . elgg_echo('groups:member') . ')');
		
		$label = elgg_view_image_block($icon, $name, ['class' => 'elgg-autocomplete-item newsletter-recipient-autocomplete-group']);
		
		// content for if selected
		$name = $recipient->name;
		$name .= elgg_format_element('span', ['class' => 'elgg-quiet'], ' (' . $recipient->getMembers(0, 0, true) . ' ' . elgg_echo('groups:member') . ')');
		
		$content = elgg_view('input/hidden', ['name' => 'group_guids[]', 'value' => $recipient->getGUID()]);
		$content .= elgg_view_image_block($icon, $name, ['image_alt' => elgg_view_icon('delete-alt')]);
		
		return [
			'type' => 'group',
			'value' => $recipient->getGUID(),
			'label' => $label,
			'content' => elgg_format_element('div', ['class' => 'newsletter-recipient-result phs'], $content),
		];
	} elseif (newsletter_is_email_address($recipient)) {
		$name = elgg_format_element('span', [], elgg_echo('newsletter:recipients:email') . ': ') . $recipient;
		$label = elgg_view_image_block('', $name, ['class' => 'elgg-autocomplete-item newsletter-recipient-autocomplete-email']);
			
		$content = elgg_view('input/hidden', ['name' => 'emails[]', 'value' => $recipient]);
		$content .= elgg_view_image_block('', $recipient, ['image_alt' => elgg_view_icon('delete-alt')]);
			
		return [
			'type' => 'email',
			'label' => $label,
			'content' => elgg_format_element('div', ['class' => 'newsletter-recipient-result phs'], $content),
		];
	}
	
	return false;
}

/**
 * Get all the people that subscribed to the newsletter of this container
 *
 * @param ElggEntity $container Which container
 * @param bool       $count	    Return just a count, not the actual subscribers
 *
 * @return array | int On success or false on failure
 */
function newsletter_get_subscribers(ElggEntity $container, $count = false) {
	
	if (!elgg_instanceof($container, 'site') && !elgg_instanceof($container, 'group')) {
		return false;
	}
	
	// get the subscribers
	if (!$count) {
		$result = ['users' => [], 'emails' => []];
		
		// get all subscribed community members
		// @todo make this easier????
		$tmp_users = elgg_get_entities_from_relationship([
			'type' => 'user',
			'selects' => ['ue.email'],
			'site_guids' => false,
			'limit' => false,
			'relationship' => NewsletterSubscription::SUBSCRIPTION,
			'relationship_guid' => $container->getGUID(),
			'inverse_relationship' => true,
			'joins' => ['JOIN ' . elgg_get_config('dbprefix') . 'users_entity ue ON e.guid = ue.guid'],
			'callback' => 'newsletter_user_row_to_subscriber_info',
		]);
		if (!empty($tmp_users)) {
			foreach ($tmp_users as $tmp_user) {
				$result['users'][$tmp_user['guid']] = $tmp_user['email'];
			}
		}
		
		// check the email subscriptions
		$result['emails'] = elgg_get_entities_from_relationship([
			'type' => 'object',
			'subtype' => NewsletterSubscription::SUBTYPE,
			'selects' => ['oe.title'],
			'limit' => false,
			'relationship' => NewsletterSubscription::SUBSCRIPTION,
			'relationship_guid' => $container->getGUID(),
			'inverse_relationship' => true,
			'joins' => ['JOIN ' . elgg_get_config('dbprefix') . 'objects_entity oe ON e.guid = oe.guid'],
			'callback' => 'newsletter_subscription_row_to_subscriber_info',
		]);
	} else {
		// get all subscribed community members
		$result = elgg_get_entities_from_relationship([
			'type' => 'user',
			'site_guids' => false,
			'count' => true,
			'relationship' => NewsletterSubscription::SUBSCRIPTION,
			'relationship_guid' => $container->getGUID(),
			'inverse_relationship' => true,
		]);
		
		// check the email subscriptions
		$result += elgg_get_entities_from_relationship([
			'type' => 'object',
			'subtype' => NewsletterSubscription::SUBTYPE,
			'count' => true,
			'relationship' => NewsletterSubscription::SUBSCRIPTION,
			'relationship_guid' => $container->getGUID(),
			'inverse_relationship' => true,
		]);
	}
	
	return $result;
}

/**
 * Check if a user is subscribed to a container entity
 *
 * @param ElggUser   $user   The user to check
 * @param ElggEntity $entity The container entity to check against
 *
 * @return boolean True => the user has a subscription, false => no subscription or error
 */
function newsletter_check_user_subscription(ElggUser $user, ElggEntity $entity) {
	
	if (!elgg_instanceof($user, 'user')) {
		return false;
	}
	
	if (!elgg_instanceof($entity, 'site') && !elgg_instanceof($entity, 'group')) {
		return false;
	}
	
	// include all users
	if (newsletter_include_existing_users()) {
		// exclude if blocked
		return !((bool) check_entity_relationship($user->getGUID(), NewsletterSubscription::BLACKLIST, $entity->getGUID()));
	} else {
		// only if opt-in
		return (bool) check_entity_relationship($user->getGUID(), NewsletterSubscription::SUBSCRIPTION, $entity->getGUID());
	}
}

/**
 * Add a subscription for an user to a container
 *
 * @param ElggUser   $user   The user to subscribe
 * @param ElggEntity $entity The container entity to subscribe to
 *
 * @return boolean true on success else false
 */
function newsletter_subscribe_user(ElggUser $user, ElggEntity $entity) {
	
	if (!elgg_instanceof($user, 'user')) {
		return false;
	}
	
	if (!elgg_instanceof($entity, 'site') && !elgg_instanceof($entity, 'group')) {
		return false;
	}
	
	// check if subscribed
	if (!check_entity_relationship($user->getGUID(), NewsletterSubscription::SUBSCRIPTION, $entity->getGUID())) {
		// not yet, so add
		$result = add_entity_relationship($user->getGUID(), NewsletterSubscription::SUBSCRIPTION, $entity->getGUID());
	} else {
		$result = true;
	}
	
	// remove blocklist relation
	remove_entity_relationship($user->getGUID(), NewsletterSubscription::BLACKLIST, $entity->getGUID());
	
	// remove general blocklist
	$site = elgg_get_site_entity();
	remove_entity_relationship($user->getGUID(), NewsletterSubscription::GENERAL_BLACKLIST, $site->getGUID());
	
	// check if on email blacklist
	$subscription = newsletter_get_subscription($user->email);
	
	if (!empty($subscription)) {
		$subscription->removeRelationship($entity, NewsletterSubscription::BLACKLIST);
	}
	
	return $result;
}

/**
 * Add a subscription for an email address to a container
 *
 * @param string     $email  The email address to add to the subscriptions
 * @param ElggEntity $entity The container entity to subscribe to
 *
 * @return boolean true on success else false
 */
function newsletter_subscribe_email($email, ElggEntity $entity) {
	
	if (!newsletter_is_email_address($email)) {
		return false;
	}
	
	if (!elgg_instanceof($entity, 'site') && !elgg_instanceof($entity, 'group')) {
		return false;
	}
	
	// check if email belongs to existing user
	$users = get_user_by_email($email);
	if (!empty($users)) {
		return newsletter_subscribe_user($users[0], $entity);
	}

	// check if email address exists in the system
	$subscription = newsletter_get_subscription($email);
	
	if (empty($subscription)) {
		$subscription = new NewsletterSubscription();
		$subscription->title = $email;
		
		if (!$subscription->save()) {
			return false;
		}
	}
	
	// subscribe
	$result = (bool) $subscription->addRelationship($entity->getGUID(), NewsletterSubscription::SUBSCRIPTION);
	
	// remove blocklist relation
	remove_entity_relationship($subscription->getGUID(), NewsletterSubscription::BLACKLIST, $entity->getGUID());
	
	// remove general blocklist
	$site = elgg_get_site_entity();
	remove_entity_relationship($subscription->getGUID(), NewsletterSubscription::GENERAL_BLACKLIST, $site->getGUID());
	
	return $result;
}

/**
 * Remove a subscription for an user to a container
 *
 * @param ElggUser   $user   The user to unsubscribe
 * @param ElggEntity $entity The container entity to unsubscribe from
 *
 * @return boolean true on success else false
 */
function newsletter_unsubscribe_user(ElggUser $user, ElggEntity $entity) {
	
	if (!elgg_instanceof($user, 'user')) {
		return false;
	}
	
	if (!elgg_instanceof($entity, 'site') && !elgg_instanceof($entity, 'group')) {
		return false;
	}
	
	// remove subscription
	remove_entity_relationship($user->getGUID(), NewsletterSubscription::SUBSCRIPTION, $entity->getGUID());
	
	// check if on email subscriptionlist
	$subscription = newsletter_get_subscription($user->email);
	
	if (!empty($subscription)) {
		$subscription->removeRelationship($entity->getGUID(), NewsletterSubscription::SUBSCRIPTION);
	}

	// check if blocked
	if (!check_entity_relationship($user->getGUID(), NewsletterSubscription::BLACKLIST, $entity->getGUID())) {
		// not yet, so add
		return add_entity_relationship($user->getGUID(), NewsletterSubscription::BLACKLIST, $entity->getGUID());
	}
	
	return true;
}

/**
 * Remove a subscription for an email address to a container
 *
 * @param string     $email  The email address to remove from the subscriptions
 * @param ElggEntity $entity The container entity to unsubscribe from
 *
 * @return boolean true on success else false
 */
function newsletter_unsubscribe_email($email, ElggEntity $entity) {
	
	if (!newsletter_is_email_address($email)) {
		return false;
	}
	
	if (!elgg_instanceof($entity, 'site') && !elgg_instanceof($entity, 'group')) {
		return false;
	}
	
	// check if not existing user
	$users = get_user_by_email($email);
	if (!empty($users)) {
		// existing user
		return newsletter_unsubscribe_user($users[0], $entity);
	}

	// email address
	$subscription = newsletter_get_subscription($email);
	
	if (empty($subscription)) {
		$subscription = new NewsletterSubscription();
		$subscription->title = $email;
		
		if (!$subscription->save()) {
			return false;
		}
	}
	
	// remove existing subscription (if any)
	$subscription->removeRelationship($entity->getGUID(), NewsletterSubscription::SUBSCRIPTION);
	
	// add to blocked list
	return $subscription->addRelationship($entity->getGUID(), NewsletterSubscription::BLACKLIST);
}

/**
 * Custom callback for elgg_get_* function to return a subset of information about an user
 *
 * @param stdObj $row A database row
 *
 * @return array contains [guid] => email
 *
 * @see elgg_get_entities()
 */
function newsletter_user_row_to_subscriber_info($row) {
	return [
		'guid' => (int) $row->guid,
		'email' => $row->email,
	];
}

/**
 * Custom callback for elgg_get_* function to return the email address of a subscriber
 *
 * @param stdObj $row A database row
 *
 * @return string the email address of the subscriber
 *
 * @see elgg_get_entities()
 */
function newsletter_subscription_row_to_subscriber_info($row) {
	return $row->title;
}

/**
 * Custom callback for elgg_get_* function to return the GUID of an entity
 *
 * @param stdObj $row A database row
 *
 * @return int The GUID of the entity
 *
 * @see elgg_get_entities()
 */
function newsletter_row_to_guid($row) {
	return (int) $row->guid;
}

/**
 * A different interpretation of is_email_address()
 * because PHP doesn't always correctly verify email addresses
 *
 * @param string $address The email address to check
 *
 * @return bool true if email, false otherwise
 *
 * @see is_email_address()
 * @see filter_var()
 */
function newsletter_is_email_address($address) {
	
	if (empty($address)) {
		return false;
	}
	
	$regexpr = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/";
	
	return (bool) preg_match($regexpr, $address);
}

/**
 * Generate an URL so the recipient can directly unsubscribe from a newsletter
 *
 * @param ElggEntity $container Which newsletter container (ElggSite or ElggGroup)
 * @param string|int $recipient The user_guid or email address of the recipient
 *
 * @return bool|string The unsubscribe link or false on failure
 */
function newsletter_generate_unsubscribe_link(ElggEntity $container, $recipient) {

	if (!elgg_instanceof($container, 'site') && !elgg_instanceof($container, 'group')) {
		return false;
	}
	
	$result = 'newsletter/unsubscribe/' . $container->getGUID();
	
	if (!empty($recipient)) {
		$code = newsletter_generate_unsubscribe_code($container, $recipient);
		
		if (is_numeric($recipient)) {
			// recipient is an user_guid
			$result .= '?u=' . $recipient . '&c=' . $code;
		} elseif (newsletter_is_email_address($recipient)) {
			// recipient is an email address
			$result .= '?e=' . $recipient . '&c=' . $code;
		}
	}
	
	return elgg_normalize_url($result);
}

/**
 * Generate a unsubscribe code to be used in validation
 *
 * @param ElggEntity $container Which newsletter container (ElggSite or ElggGroup)
 * @param string|int $recipient The user_guid or email address of the recipient
 *
 * @return bool|string The unsubscribe code or false on failure
 */
function newsletter_generate_unsubscribe_code(ElggEntity $container, $recipient) {
	
	if (!elgg_instanceof($container, 'site') && !elgg_instanceof($container, 'group')) {
		return false;
	}
	
	if (empty($recipient)) {
		return false;
	}
	
	// make sure we have a user_guid or email address
	if (!is_numeric($recipient) && !newsletter_is_email_address($recipient)) {
		return false;
	}
	
	if (is_numeric($recipient)) {
		$recipient = (int) $recipient;
	}
	
	$plugin = elgg_get_plugin_from_id('newsletter');
	$hmac = elgg_build_hmac([
		$container->getGUID(),
		$recipient,
		$plugin->time_created,
	]);
	return $hmac->getToken();
}

/**
 * Validate a provided unsubscribe code
 *
 * @param ElggEntity $container Which newsletter container (ElggSite or ElggGroup)
 * @param string|int $recipient The user_guid or email address of the recipient
 * @param string     $code      The unsubscribe code the recipient provided
 *
 * @return bool	true is valid or false on failure
 */
function newsletter_validate_unsubscribe_code(ElggEntity $container, $recipient, $code) {
	
	if (!elgg_instanceof($container, 'site') && !elgg_instanceof($container, 'group')) {
		return false;
	}
	
	if (empty($recipient)) {
		return false;
	}
	
	// make sure we have a user_guid or email address
	if (!is_numeric($recipient) && !newsletter_is_email_address($recipient)) {
		return false;
	}
	
	// generate the code as it should be
	$correct_code = newsletter_generate_unsubscribe_code($container, $recipient);
	
	// check for a match
	if ($code === $correct_code) {
		return true;
	}
	
	return false;
}

/**
 * Find a subscription entity for the given email address
 *
 * @param string $email The email address to find the subscription for
 *
 * @return bool|NewsletterSubscription The found subscription or false
 */
function newsletter_get_subscription($email) {
	
	if (!newsletter_is_email_address($email)) {
		return false;
	}
	
	// ignore access
	$ia = elgg_set_ignore_access(true);
	
	$entities = elgg_get_entities([
		'type' => 'object',
		'subtype' => NewsletterSubscription::SUBTYPE,
		'limit' => 1,
		'joins' => ['JOIN ' . elgg_get_config('dbprefix') . 'objects_entity oe ON e.guid = oe.guid'],
		'wheres' => ["(oe.title = '" . sanitise_string($email) . "')"],
	]);
	
	// restore access
	elgg_set_ignore_access($ia);
	
	if (!empty($entities)) {
		return $entities[0];
	}
	
	return false;
}

/**
 * Block an user from all newsletters
 *
 * @param ElggUser $user The user to block
 *
 * @return boolean True on success, false on failure
 */
function newsletter_unsubscribe_all_user(ElggUser $user) {
	
	if (!elgg_instanceof($user, 'user')) {
		return false;
	}
	
	$site = elgg_get_site_entity();
	// remove site subscription
	remove_entity_relationship($user->getGUID(), NewsletterSubscription::SUBSCRIPTION, $site->getGUID());
	
	// remove all subscriptions
	$entities = elgg_get_entities_from_relationship([
		'type' => 'group',
		'limit' => false,
		'relationship' => NewsletterSubscription::SUBSCRIPTION,
		'relationship_guid' => $user->getGUID(),
		'callback' => 'newsletter_row_to_guid',
	]);
	
	if (!empty($entities)) {
		foreach ($entities as $entity_guid) {
			remove_entity_relationship($user->getGUID(), NewsletterSubscription::SUBSCRIPTION, $entity_guid);
		}
	}
	
	// add to general blacklist
	$result = (bool) add_entity_relationship($user->getGUID(), NewsletterSubscription::GENERAL_BLACKLIST, $site->getGUID());
	
	// remove email subscriptions (if any)
	$subscription = newsletter_get_subscription($user->email);
	
	if (!empty($subscription)) {
		$subscription->delete();
	}
	
	return $result;
}

/**
 * Block an email address from all newsletters
 *
 * @param string $email The email address to block
 *
 * @return boolean True on success, false on failure
 */
function newsletter_unsubscribe_all_email($email) {
	
	if (!newsletter_is_email_address($email)) {
		return false;
	}
	
	// get subscription
	$subscription = newsletter_get_subscription($email);
	if (!empty($subscription)) {
		return false;
	}
	
	$subscription = new NewsletterSubscription();
	$subscription->title = $email;
	
	if (!$subscription->save()) {
		return false;
	}
	
	// remove all existing subscriptions
	remove_entity_relationships($subscription->getGUID(), NewsletterSubscription::SUBSCRIPTION);
	
	// add to general blacklist
	$site = elgg_get_site_entity();
		
	return (bool) add_entity_relationship($subscription->getGUID(), NewsletterSubscription::GENERAL_BLACKLIST, $site->getGUID());
}

/**
 * Convert an email subscription on the newsletters to a user setting
 *
 * @param NewsletterSubscription $subscription The found email subscription
 * @param ElggUser               $user         The user to save the new settings to
 *
 * @return bool	true on success or false on failure
 */
function newsletter_convert_subscription_to_user_setting(NewsletterSubscription $subscription, ElggUser $user) {
	
	if (!elgg_instanceof($subscription, 'object', NewsletterSubscription::SUBTYPE) || !elgg_instanceof($user, 'user')) {
		return false;
	}
	
	// check global block list
	$site = elgg_get_site_entity();
	if (check_entity_relationship($subscription->getGUID(), NewsletterSubscription::GENERAL_BLACKLIST, $site->getGUID())) {
		// copy the block all
		add_entity_relationship($user->getGUID(), NewsletterSubscription::GENERAL_BLACKLIST, $site->getGUID());
	} else {
		// check for subscriptions
		$subscriptions = $subscription->getEntitiesFromRelationship(NewsletterSubscription::SUBSCRIPTION, false, false);
			
		if (!empty($subscriptions)) {
			foreach ($subscriptions as $entity) {
				newsletter_subscribe_user($user, $entity);
			}
		}
			
		// check for blocks
		$blocked = $subscription->getEntitiesFromRelationship(NewsletterSubscription::BLACKLIST, false, false);
			
		if (!empty ($blocked)) {
			foreach ($blocked as $entity) {
				newsletter_unsubscribe_user($user, $entity);
			}
		}
	}
	
	// remove email subscription
	return (bool) $subscription->delete();
}

/**
 * Checks if group newsletter is allowed
 *
 * @param ElggGroup $group group entity
 *
 * @return bool
 */
function newsletter_is_group_enabled(ElggGroup $group = null) {
	static $plugin_setting;
	
	// make sure we only get the plugin setting once
	if (!isset($plugin_setting)) {
		$plugin_setting = false;
		
		$setting = elgg_get_plugin_setting('allow_groups', 'newsletter');
		if ($setting == 'yes') {
			$plugin_setting = true;
		}
	}
	
	// check the setting of the group (if any)
	if (!$plugin_setting) {
		return false;
	}
	
	if (!elgg_instanceof($group, 'group')) {
		// no group just checking plugin setting
		return true;
	}
	
	// check the setting of the group
	if ($group->newsletter_enable != 'no') {
		return true;
	}
		
	return false;
}

/**
 * Sends the preview newsletter
 *
 * @param Newsletter $entity newsletter to be send
 * @param string     $email  emailaddress of the recipient
 *
 * @return bool if sending is succes or not
 */
function newsletter_send_preview(Newsletter $entity, $email) {
	
	if (!elgg_instanceof($entity, "object", Newsletter::SUBTYPE) || empty($email)) {
		return false;
	}
	
	$container = $entity->getContainerEntity();
	
	// build correct subject
	if ($entity->subject) {
		$message_subject = $entity->subject;
	} else {
		$message_subject = elgg_echo('newsletter:subject', [$container->name, $entity->title]);
	}
	//  plaintext message
	$message_plaintext_content = elgg_echo('newsletter:plain_message', [$entity->getURL()]);
	
	// html content
	$message_html_content = elgg_view_layout('newsletter', ['entity' => $entity]);
	// convert to inline CSS for email clients
	$message_html_content = html_email_handler_css_inliner($message_html_content);
	
	// add unsubscribe link
	$unsubscribe_link = newsletter_generate_unsubscribe_link($container, $email);
	$message_html_content = str_ireplace(urlencode("{unsublink}"), $unsubscribe_link, $message_html_content);
	
	// replace online link
	$online_link = $entity->getURL();
	$new_online_link = $online_link . '?e=' . $email;
	
	$message_html_content = str_ireplace($online_link, $new_online_link, $message_html_content);
	
	// send preview
	return html_email_handler_send_email([
		'from' => html_email_handler_make_rfc822_address($container),
		'subject' => $message_subject,
		'plaintext_message' => $message_plaintext_content,
		'to' => $email,
		'html_message' => $message_html_content,
	]);
}

/**
 * Check a plugin setting to force existing users to opt-in for newsletters
 *
 * @return bool true if opt-in is required
 */
function newsletter_include_existing_users() {
	static $result;
	
	if (isset($result)) {
		return $result;
	}
	
	$result = true;
	if (elgg_get_plugin_setting('include_existing_users', 'newsletter') == 'no') {
		$result = false;
	}
	
	return $result;
}

/**
 * Returns all the available templates, these include those provided by themes
 * and the saved templates
 *
 * Other plugins/themes can provide their own template if the create a view
 * newsletter/templates/<some name>/{body|css}
 *
 * @param int         $container_guid The container of the current newsletter
 * @param \ElggEntity $entity         The current newsletter (optional) if editing
 *
 * @return array The available templates
 */
function newsletter_get_available_templates($container_guid, $entity = null) {
	$result = [];
	
	// detect templates provided by themes/plugins
	$locations = elgg_list_views();
	$pattern = '/^newsletter\/templates\/(?P<name>\w+)\/(body|css)$/';
	
	foreach ($locations as $view) {
		$matches = [];
		$res = preg_match($pattern, $view, $matches);
		
		if (empty($res)) {
			continue;
		}
		
		$name = elgg_extract('name', $matches);
		$lan_key = "newsletter:edit:template:select:{$name}";
		if (elgg_language_key_exists($lan_key)) {
			$title = elgg_echo($lan_key);
		} else {
			$title = $name;
		}
		
		$result[$name] = $title;
	}
	
	// get saved templates
	if (!empty($container_guid)) {
		$templates = elgg_get_entities([
			'type' => 'object',
			'subtype' => NEWSLETTER_TEMPLATE,
			'container_guid' => $container_guid,
			'limit' => false,
		]);
		if (!empty($templates)) {
			foreach ($templates as $template) {
				$result[$template->getGUID()] = $template->title;
			}
		}
	}
	
	// the custom selection option
	unset($result['custom']); // make sure custom is last in the list (shouldn't be provided by a plugin/theme)
	$result['custom'] = elgg_echo('newsletter:edit:template:select:custom');
		
	return elgg_trigger_plugin_hook('templates', 'newsletter', [
		'container_guid' => $container_guid,
		'entity' => $entity,
	], $result);
}

/**
 * Process an uploaded CSV file to find new recipients.
 *
 * @param array $recipients previous recipients, to prevent duplicates
 * Contains:
 *
 * user_guids => array() existing users
 * emails => array() extra email addresses
 *
 * @return array
 */
function newsletter_process_csv_upload(array $recipients) {
	
	// is a file uploaded
	if (!get_uploaded_file('csv')) {
		return $recipients;
	}
	
	// open the file as CSV
	$fh = fopen($_FILES['csv']['tmp_name'], 'r');
	
	if (empty($fh)) {
		return $recipients;
	}
	
	$email_column = false;
	
	// try to find an email column (in the first 2 rows)
	for ($i = 0; $i < 2; $i++) {
		$row = fgetcsv($fh, null, ';', '\'');
		if (!empty($row)) {
			foreach ($row as $index => $field) {
				if (newsletter_is_email_address($field)) {
					$email_column = $index;
					break;
				}
			}
		}
	}
	
	// found an email column
	if ($email_column === false) {
		// no email column found, report this
		system_message(elgg_echo('newsletter:csv:no_email'));
		
		return $recipients;
	}
	
	$counter = 0;
	
	// start at the beginning
	if (!rewind($fh)) {
		return $recipients;
	}
	
	$row = fgetcsv($fh, null, ';', '\'');
	while ($row !== false) {
		// get the email address
		$email = @$row[$email_column];
		
		// make sure it's a valid email address
		if (newsletter_is_email_address($email)) {
			$counter++;
			$exists = false;
			
			// is this email address already in the recipients list
			if (in_array($email, $recipients['emails'])) {
				$exists = true;
			} else {
				// check for an existing user
				$ia = elgg_set_ignore_access(true);
				
				$users = get_user_by_email($email);
				if (!empty($users)) {
					foreach ($users as $user) {
						if (in_array($user->getGUID(), $recipients['user_guids'])) {
							$exists = true;
						}
					}
				}
				
				elgg_set_ignore_access($ia);
			}
			
			if ($exists === false) {
				// email address wasn't added yet
				// so add to the list
				$ia = elgg_set_ignore_access(true);
				
				$users = get_user_by_email($email);
				if (!empty($users)) {
					$recipients['user_guids'][] = $users[0]->getGUID();
				} else {
					$recipients['emails'][] = $email;
				}
				
				elgg_set_ignore_access($ia);
			}
		}
		
		// go to the next row
		$row = fgetcsv($fh, null, ';', '\'');
	}
	
	// done, report the added emails
	system_message(elgg_echo('newsletter:csv:added', [$counter]));
	
	return $recipients;
}

/**
 * Get the plugin settings for URL postfix
 *
 * @return bool|array
 */
function newsletter_get_url_postfix() {
	static $result;
	
	if (isset($result)) {
		return $result;
	}
	
	$result = false;
	
	$url_postfix = elgg_get_plugin_setting('url_postfix', 'newsletter');
	
	if (!is_null($url_postfix) && ($url_postfix !== false)) {
		$lines = explode(PHP_EOL, $url_postfix);
		$temp_result = [];
		
		foreach ($lines as $line) {
			list($name, $value) = explode('=', $line);
			
			if (is_null($name) || ($name === false)) {
				continue;
			}
			if (is_null($value) || ($value === false)) {
				continue;
			}
			
			$temp_result[$name] = $value;
		}
		
		if (!empty($temp_result)) {
			$result = $temp_result;
		}
	}
	return $result;
}

/**
 * Add the URL postfix to all url's in the newsletter content
 *
 * @param string     $html_content the content of the newletter
 * @param Newsletter $newsletter   the source newsletter to use for some replacements in the postfix
 *
 * @return string
 */
function newsletter_apply_url_postfix($html_content, Newsletter $newsletter) {
	static $pattern;
	
	if (!($newsletter instanceof Newsletter)) {
		return $html_content;
	}
	
	// get the postfix settings
	$url_postfix_settings = newsletter_get_url_postfix();
	if (empty($url_postfix_settings)) {
		return $html_content;
	}
	
	// build the pattern once
	if (!isset($pattern)) {
		// convert site url to preg friendly version
		$preg_site_url = elgg_get_site_url();
		$preg_site_url = substr($preg_site_url, 0, -1);
		$preg_site_url = str_replace("/", "\\/", $preg_site_url);
		$preg_site_url = str_replace(".", "\\.", $preg_site_url);
		
		$pattern = '/\shref=([\'"]' . $preg_site_url . '[^\'"]*[\'"])/i';
	}
	
	// find all matches
	$matches = [];
	preg_match_all($pattern, $html_content, $matches);
	
	if (empty($matches) || !isset($matches[1])) {
		return $html_content;
	}
	
	// url postfix placeholder replacements
	$replacements = ['{guid}' => $newsletter->getGUID()];
	
	foreach ($url_postfix_settings as $name => $value) {
		$url_postfix_settings[$name] = str_ireplace(array_keys($replacements), array_values($replacements), $value);
	}
	
	// go through all the matches
	$urls = $matches[1];
	$urls = array_unique($urls);
	
	foreach ($urls as $url) {
		// remove wrapping quotes from the url
		$real_url = substr($url, 1, -1);
		// add the postfix params
		$new_url = elgg_http_add_url_query_elements($real_url, $url_postfix_settings);
		// make the correct replacement string
		$replacement = str_replace($real_url, $new_url, $url);
		
		// replace the url in the content
		$html_content = str_replace($url, $replacement, $html_content);
	}
	
	return $html_content;
}

/**
 * Check if embed functionality is availble for Newslettes
 *
 * Eg require blog or static
 *
 * @return bool
 */
function newsletter_embed_available() {
	static $result;
	
	if (isset($result)) {
		return $result;
	}
	
	$result = false;
	
	if (elgg_is_active_plugin('blog')) {
		$result = true;
	} elseif (elgg_is_active_plugin('static')) {
		$result = true;
	}
	
	return $result;
}

/**
 * Display conten in the correct layout for embedding in Newsletter
 *
 * @param ElggEntity $entity the entity to embed
 * @param array      $vars   optional variables to pass to the embed view
 *
 * @return bool|string
 */
function newsletter_view_embed_content(ElggEntity $entity, $vars = array()) {
	
	if (!elgg_instanceof($entity)) {
		return false;
	}
	
	if (!is_array($vars)) {
		$vars = [];
	}
	
	$vars['entity'] = $entity;
	
	$type = $entity->getType();
	$subtype = $entity->getSubtype();
	
	if (!empty($subtype) && elgg_view_exists("newsletter/embed/" . $type . "/" . $subtype)) {
		return elgg_view("newsletter/embed/" . $type . "/" . $subtype, $vars);
	} elseif (elgg_view_exists("newsletter/embed/" . $type . "/default")) {
		return elgg_view("newsletter/embed/" . $type . "/default", $vars);
	} elseif (elgg_view_exists("newsletter/embed/default")) {
		return elgg_view("newsletter/embed/default", $vars);
	}
	
	return false;
}

/**
 * Check the plugin setting for custom from addresses
 *
 * @return bool
 */
function newsletter_custom_from_enabled() {
	static $result;
	
	if (isset($result)) {
		return $result;
	}
	
	$result = false;
	if (elgg_get_plugin_setting('custom_from', 'newsletter') === 'yes') {
		$result = true;
	}
	
	return $result;
}

/**
 * Validate the custom from email address
 *
 * This also triggers a plugin hook 'from_email', 'newsletter' for other plugins to hook into
 * Supplied params:
 * - email: the email address to validate
 *
 * @param string $from_email the email address to check
 *
 * @return bool
 */
function newsletter_validate_custom_from($from_email) {
	
	if (empty($from_email)) {
		// empty is allowed, sending will fallback to container
		return true;
	}
	
	if (!newsletter_is_email_address($from_email)) {
		// not an email address, always fail
		return false;
	}
	
	$result = true;
	
	// check plugin settings domain limitations
	$plugin_setting = elgg_get_plugin_setting('custom_from_domains', 'newsletter');
	if (!empty($plugin_setting)) {
		$result = false;
		$plugin_setting = string_to_tag_array($plugin_setting);
		
		list(, $domain) = explode('@', $from_email);
		foreach ($plugin_setting as $allowed_domain) {
			if ($domain === $allowed_domain) {
				// custom from is from an allowed domain
				$result = true;
				break;
			}
		}
	}
	
	// trigger a plugin hook so others are allowed to validate
	return (bool) elgg_trigger_plugin_hook('from_email', 'newsletter', ['email' => $from_email], $result);
}
