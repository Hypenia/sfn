<?php

if (elgg_is_logged_in()) {
	return;
}

?>
<p class="mrdave-aalborg-login"><?= elgg_echo('mrdave_aalborg:login_to_comment'); ?></p>
