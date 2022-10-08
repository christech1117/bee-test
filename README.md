## About this project
It is made by PHP framework: Laravel 9.x.  
You can start up it with docker.  
Use commands to publish and consume messages when it is running successfully. 

## Requirement
Docker and Docker compose  
PHP: ^8.0  
Mysql: 5.7

## Installation
1. Copy file `.env.example` to `.env`.
2. If you own mysql and rabbitmq, edit information in .env 
```dotenv
# mysql
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=fcm
DB_USERNAME=root
DB_PASSWORD=123456

# rabbitmq connection
RABBITMQ_HOST=rabbitmq
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
```
3. change path of google application credentials in .env
```dotenv
# google APPLICATION_CREDENTIALS
GOOGLE_APPLICATION_CREDENTIALS=/app/resources/credentials/firebase_credentials.json
```

4. Start containers.
```shell
$ cd /path/to/bee && docker composer up -d
```
5. Install php composer packages just once.
```shell
$ docker compose exec -u www-data php-fcm-flow composer install
```
6. If you want to change queue name, edit the file `config/rabbitmq.php`.
```shell
<?php

return [
    'fcm' => [
        'host' => env('RABBITMQ_HOST', 'rabbitmq'),
        'port' => env('RABBITMQ_PORT', 5672),
        'user' => env('RABBITMQ_USER', 'guest'),
        'password' => env('RABBITMQ_PASSWORD', 'guest'),
        'queue' => 'notification.fcm', // queue name for notification.fcm
    ],
    'done' => [
        'host' => env('RABBITMQ_HOST', 'rabbitmq'),
        'port' => env('RABBITMQ_PORT', 5672),
        'user' => env('RABBITMQ_USER', 'guest'),
        'password' => env('RABBITMQ_PASSWORD', 'guest'),
        'queue' => 'notification.done', // queue name for notification.done
    ],
];
```
## How to use
1. Command for starting the consumer. It is a long-running command. 
```shell
$ docker compose exec -u www-data php-fcm-flow php artisan consumer:fcm
```
If message is validated, it will be saved to table `fcm_jobs` and published to queue notification.done.  
If message is `not` validated, it will be saved to table `failed_fcm_jobs`.  
Whatever, the message will be acknowledged.

2. Send some messages to queue notification.fcm for simulating.
```shell
docker compose exec -u www-data php-fcm-flow php artisan publish:fcm <optional: your-message>
```

3. Open `http://localhost:15672` in browser to check messages in Rabbitmq management.

4. Open `storage/logs/laravel-YYYY-MM-DD.log` to check the auto-generated log.
```text
[2022-10-08 04:31:26] local.INFO: received message: {"identifier": "fcm-msg-a1beff5ac", "type": "device", "deviceId": "string", "text":"Notification message"}  
[2022-10-08 04:31:26] local.INFO: publish message to queue notification.done:{"identifier": "fcm-msg-a1beff5ac", "type": "device", "deviceId": "string", "text":"Notification message"}  
[2022-10-08 04:31:26] local.INFO: publish message to fcm:{"identifier": "fcm-msg-a1beff5ac", "type": "device", "deviceId": "string", "text":"Notification message"}  
[2022-10-08 04:31:27] local.INFO: save message to fcm_jobs:{"identifier": "fcm-msg-a1beff5ac", "type": "device", "deviceId": "string", "text":"Notification message"}  
```
