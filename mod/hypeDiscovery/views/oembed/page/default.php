<?php

elgg_set_viewtype('default');

// in case apache is configured to setifempty
header("X-Frame-Options: ALLOWALL", true);

header("Content-type: text/html; charset=UTF-8");

$lang = get_current_language();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $lang; ?>" lang="<?php echo $lang; ?>">
	<head>
		<?php echo elgg_view('page/elements/head'); ?>
	</head>
	<body class="elgg-oembed">
		<?php echo elgg_extract('body', $vars); ?>
		<?php echo elgg_view('page/elements/foot'); ?>
	</body>
</html>