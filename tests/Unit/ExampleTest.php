<?php

namespace Tests\Unit;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_example()
    {
        $messaging = app('firebase.messaging');

        $message = CloudMessage::withTarget('topic', 'topic')
            ->withNotification(Notification::create('Title', 'Body'))
            ->withData(['key' => 'value']);

        $messaging->send($message);

        $this->assertTrue(true);
    }
}
