<?php

namespace hypeJunction\Discovery;

if (!elgg_is_active_plugin('data_views')) {
	return;
}

$entity = elgg_extract('entity', $vars);

$maxwith = get_input('maxwidth');
$maxheight = get_input('maxheight');

echo data_views_array_to_xml(get_oembed_response($entity, 'xml', $maxwidth, $maxheight), 'oembed');