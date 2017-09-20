<?php

require __DIR__ . "/../vendor/autoload.php";

error_reporting(E_ALL);

$uri = "tcp://guest:guest@localhost:61613";
$client = new Amp\Stomp\Client($uri);

\Amp\Loop::run(function () use ($client) {
    yield $client->connect();

    // schedule a message send every half second
    \Amp\Loop::repeat(500, function () use ($client) {
        yield $client->send("/exchange/stomp-test/foo.bar", "mydata");
    });

    // subscribe to the messages we're sending
    yield $client->subscribe("/exchange/stomp-test/*.*", [
        'ack' => 'client',
    ]);

    // dump all messages we receive to the console
    while (true) {
        /** @var \Amp\Stomp\Frame $frame */
        $frame = yield $client->read();
        echo "{$frame}\n\n";
        yield $client->ack($frame->getHeaders()['ack']);
    }
});
