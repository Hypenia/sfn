<?php

function freichat_init()
{
    elgg_extend_view('page/elements/body', 'chat/freichat');
}

// return function() does not work with Elgg 2.x, let's keep this till Elgg 2.x is supported, later it can be uncommented.
//return function () {
elgg_register_event_handler('init', 'system', 'freichat_init');
//};