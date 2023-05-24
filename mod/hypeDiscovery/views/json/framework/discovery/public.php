<?php

namespace hypeJunction\Discovery;

$entity = elgg_extract('entity', $vars);

$maxwith = get_input('maxwidth');
$maxheight = get_input('maxheight');

echo json_encode(get_oembed_response($entity, 'json', $maxwidth, $maxheight));