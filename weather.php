<?php
header('Content-Type: application/json');

require 'vendor/autoload.php';

use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => 'https://api.weatherapi.com'
]);

$response = $client->get(
    "/v1/forecast.json?key=$api_key&q=$postal_code&days=3&aqi=no&alerts=no",
    [
        'http_errors' => false
    ]
);

if ($response->getStatusCode() !== 200) {
    http_response_code(500);
    return;
}

$data = json_decode($response->getBody()->getContents(), true);

$result = [];

$result['hr'] = intval(date('G', $data['location']['localtime_epoch']));
$result['min'] = intval(date('i', $data['location']['localtime_epoch']));
$result['sec'] = intval(date('s', $data['location']['localtime_epoch']));

$result['current'] = [];
$result['current']['temperature'] = $data['current']['temp_c'];
$result['current']['icon'] = '/weather' .
    ($data['current']['is_day'] === 1 ? '/day' : '/night') .
    str_replace('png', 'jpg', strrchr($data['current']['condition']['icon'], '/'));
$result['current']['feelslike'] = $data['current']['feelslike_c'];

$result['hour'] = [];

$hour_count = 0;
$hour_max = 7;
foreach ($data['forecast']['forecastday'] as $day) {
    foreach ($day['hour'] as $hour) {
        if ($hour_count > $hour_max) {
            break;
        }
        if ($hour['time_epoch'] > $data['location']['localtime_epoch']) {
            array_push($result['hour'], [
                'time' => date('G', $hour['time_epoch']),
                'temperature' => $hour['temp_c'],
                'icon' => '/weather' .
                    ($hour['is_day'] === 1 ? '/day' : '/night') .
                    str_replace('png', 'jpg', strrchr($hour['condition']['icon'], '/')),
            ]);
            $hour_count++;
        }
    }
}

$result['day'] = [];

foreach ($data['forecast']['forecastday'] as $day) {
    $date = new DateTime('now', new DateTimeZone('UTC'));
    $date->setTimestamp($day['date_epoch']);

    array_push($result['day'], [
        'time' => $date->format('D'),
        'high' => $day['day']['maxtemp_c'],
        'low' => $day['day']['mintemp_c'],
        'icon' => '/weather/day' .
            str_replace('png', 'jpg', strrchr($day['day']['condition']['icon'], '/')),
    ]);
}

try {
    require('ecobee.php');
    $result['current']['indoor'] = indoor_temp();
} catch (Exception $ex) {
    http_response_code(500);
    return;
}

print(json_encode($result));
?>
