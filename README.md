# netatmo2mqtt

PHP based netatmo weather API to mqtt gateway.

Please add local .env just like _.env.example_ somewhere near and run:
 
 `php pooler.phar`

Can use plain pooler.php instead of phar too.

Add pooler to crontab to get actual data for each 1min


```
*/1 * * * * cd ~/netatmo2mqtt/ && php ./pooler.phar
```

.env example:

```
NETATMO_CLIENTID=111111112222222223333333333333444444
NETATMO_CLIENTSECRET=111111112222222223333333333333444444
NETATMO_USERNAME=user@example.com
NETATMO_PASSWORD=password

MQTT_HOST=127.0.0.1
MQTT_PORT=1883
MQTT_USERNAME=
MQTT_PASSWORD=
MQTT_CLIENT_ID=netatmopooler
MQTT_DEBUG=0
```


## MQTT

Check `netatmo/WS/#` topic to get data from netatmo API
