<?php require __DIR__ . '/vendor/autoload.php';

use karpy47\PhpMqttClient\MQTTClient;


try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    //Netatmo client configuration
    $client = new Netatmo\Clients\NAApiClient(['client_id' => getenv('NETATMO_CLIENTID'),
        "client_secret" => getenv('NETATMO_CLIENTSECRET'),
        "username" => getenv('NETATMO_USERNAME'),
        "password" => getenv('NETATMO_PASSWORD')]);

    $mqttClient = new MQTTClient(getenv('MQTT_HOST') ?: '127.0.0.1', (getenv('MQTT_PORT') ?: 1883));

    if ($username = getenv('MQTT_USERNAME')) {

        $mqttClient->setAuthentication($username, getenv('MQTT_PASSWORD'));
    }

    $mqttClient->setDebug((bool)getenv('MQTT_DEBUG') ?: 0);

    if ($mqttClient->sendConnect(getenv('MQTT_CLIENT_ID') ?: 'netatmopooler')) {

        $publish = function ($topic, $data) use ($mqttClient) {
            /** @var $mqttClient MqttClient */

            return $mqttClient->sendPublish('netatmo/' . $topic, \json_encode($data));
        };


        //Authentication with Netatmo server (OAuth2)
        $client->getAccessToken();

        // fetch WS data
        $data = $client->api('getstationsdata', 'GET', array(null, true));

        if (array_key_exists('user', $data)) {
            $publish('WS/user', $data['user']);
        }

        if (array_key_exists('devices', $data)) {
            foreach ($data['devices'] as $device) {
                $id = $device['_id'];
                $white_data = ['station_name', 'date_setup', 'last_setup', 'type', 'last_status_store', 'module_name', 'firmware', 'last_upgrade', 'wifi_status', 'reachable', 'co2_calibrating', 'place'];

                $device_data = array_intersect_key($device, array_combine($white_data, array_fill(0, count($white_data), 1)));

                $publish('WS/device/' . $id, $device_data);
                $publish('WS/data/' . $id, $device['dashboard_data']);

                foreach ($device['modules'] as $module) {
                    $id = $module['_id'];
                    $white_data = ['module_name', 'date_type', 'last_setup', 'type', 'battery_percent', 'battery_vp', 'module_name', 'firmware', 'last_message', 'last_seen', 'rf_status', 'reachable', 'co2_calibrating'];

                    $module_data = array_intersect_key($module, array_combine($white_data, array_fill(0, count($white_data), 1)));

                    $publish('WS/module/' . $id, $module_data);
                    $publish('WS/data/' . $id, $module['dashboard_data']);
                }
            }


        }

        $mqttClient->sendDisconnect();
    } else {
        error_log('Can\'t connect');
    }
    $mqttClient->close();

} catch (\Exception $e) {
    error_log($e->getMessage());
}