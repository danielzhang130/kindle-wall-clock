<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;

function indoor_temp() {
    return f2c(get_response()["thermostatList"][0]["remoteSensors"][2]["capability"][0]["value"]/10);
}

function f2c($f) {
    return round(($f-32)/1.8, 1);
}

function get_response() {
    require 'constants.php';

    $client = new Client([
        'base_uri' => 'https://api.ecobee.com'
    ]);

    $response = $client->get(
        '/1/thermostat?json={"selection":{"selectionType":"registered","selectionMatch":"","includeSensors":"true"}}',
        [
            'headers' => [
                'Content-Type' => 'application/json;charset=UTF-8',
                'Authorization' => 'Bearer '.$app
            ],
            'http_errors' => false
        ]
    );

    $status_code = $response->getStatusCode();
    $data = json_decode($response->getBody()->getContents(), true);

    if ($status_code != 200) {
        if ($status_code == 500) {
            refresh();
            $data = get_response();
        } else {
            throw new RuntimeException($status_code . $data);
        }
    }
    return $data;
}

function refresh() {
    require 'constants.php';

    $client = new Client([
        'base_uri' => 'https://api.ecobee.com'
    ]);

    $response = $client->post(
        "/token?grant_type=refresh_token&code=$refresh&client_id=$key",
        [
            'http_errors' => false
        ]
    );

    $status_code = $response->getStatusCode();
    $data = json_decode($response->getBody()->getContents(), true);

    if ($status_code != 200) {
        throw new RuntimeException($status_code . $data);
    }

    $refresh_token = $data['refresh_token'];
    $access_token = $data['access_token'];

    $file = "<?php \$key = '$key';\n\$refresh = '$refresh_token'; \n\$app = '$access_token';\n?>\n";
    file_put_contents('constants.php', $file);
}
?>
