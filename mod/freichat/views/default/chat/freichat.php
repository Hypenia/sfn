<?php

$token = elgg_get_plugin_setting('token', 'freichat');
$baseUrl = elgg_get_plugin_setting('baseUrl', 'freichat');

if ($token != null && elgg_is_logged_in()) {

    $user = elgg_get_logged_in_user_entity();

    $id = elgg_get_logged_in_user_guid();
    $name = base64_encode($user->getDisplayName());
    $avatar = base64_encode($user->getIconURL("small"));
    $pubKey = explode("$$", $token)[0];
    $change = date('Ymd');


    ?>

    <script type="text/javascript">
        import('<?php echo "$baseUrl/v1/freichat-float.js?pubKey=$pubKey&userId=$id&displayName=$name&displayImage=$avatar&change=$change"; ?>');
    </script>

<?php } ?>