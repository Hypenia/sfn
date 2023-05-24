<?php

$email = get_input('email');

$ha = access_get_show_hidden_status();
access_show_hidden_entities(true);

$user = get_user_by_email($email);

access_show_hidden_entities($ha);

$data = [
	'is_registered' => !empty($user),
];
return elgg_ok_response($data);