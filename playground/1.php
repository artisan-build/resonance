<?php

use ArtisanBuild\Resonance\Resonance;

require __DIR__.'/../../../../vendor/autoload.php';

$connection = Resonance::connect();

$connection->subscribe('chat');

$connection->send([
    'event' => 'client-chat',
    'channel' => 'chat',
    'data' => [
        'text' => 'Hello, world!',
    ],
]);

$connection->unsubscribe('chat');

$connection->disconnect();

var_dump($connection->connection->receivedMessages);
