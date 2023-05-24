<?php

if (!elgg_is_active_plugin('hypeDiscovery')) {
	return;
}

echo elgg_view_form('discovery/share', [], $vars);