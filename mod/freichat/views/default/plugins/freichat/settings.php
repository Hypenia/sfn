<?php
/**
 * FreiChat settings
 *
 * @package FreiChat
 */

$plugin = elgg_extract('entity', $vars);

$token = $plugin->token ?: '';
echo elgg_view_field([
    '#type' => 'text',
    '#label' => elgg_echo('freichat:settings:token'),
    '#help' => elgg_view_icon('help') . elgg_echo('freichat:settings:token:help'),
    'name' => 'params[token]',
    'value' => $token,
]);

