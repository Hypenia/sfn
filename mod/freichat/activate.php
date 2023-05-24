<?php

/**
 * Generates a token for secure communication
 * If there is some error during token generation, that error is saved in settings in db
 * @return array
 *
 * @since version
 */
function generateToken()
{
    $data = array(
        'domain' => $_SERVER['HTTP_HOST'],
        'platform' => 'Elgg'
    );

    $payload = json_encode($data);

    $baseUrl = "https://nodelb.freichat.com/api";

    try {
        $ch = curl_init("$baseUrl/v1/keys/free/register");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        // Set HTTP Header for POST request
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload))
        );

        // Submit the POST request
        $key = curl_exec($ch);
    } catch (Exception $e) {
        return array("key" => null, "error" => $e->getMessage());
    }

    elgg_set_plugin_setting("baseUrl", $baseUrl, "freichat");
    return array("key" => $key, "error" => null);
}

$data = generateToken();

if ($data['error'] == null) {
    elgg_set_plugin_setting("token", $data['key'], "freichat");
} else {
    elgg_set_plugin_setting("error", $data['error'], "freichat");
}
