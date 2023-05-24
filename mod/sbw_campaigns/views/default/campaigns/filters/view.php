<?php

$vars['route'] = 'campaigns/view';
$vars['sort_by'] = 'priority';
$vars['class'] = 'campaigns-filter';

echo elgg_view_menu('filter', $vars);