<?php

use Webravo\Persistence\Service\RabbitMQService;

class RabbitMQServiceRPCTestTest extends TestCase
{
    public function testRabbitMQServiceRPC() {

        $execution_id = (new Datetime())->format('H:i:s');

        $publisherService = new RabbitMQService();
        $publisherService->publishMessage('Test to be deleted', 'test-rpc');

        $subscriberService = new RabbitMQService();
        $subscriberService->purgeQueue('test-rpc');
        $jobs_waiting = $subscriberService->createQueue('test-rpc');

        echo " n. " . $jobs_waiting . " message in queue (1)\n";

        $this->assertEquals($jobs_waiting,0,'Queue is not empty');

        $publisherService->createQueue('test-rpc-reply');

        $message_id = 'MSG' . rand(1000,9999);

        $header = [
            'reply_to' => 'test-rpc-reply',
            'correlation_id' => $message_id
        ];

        $publisherService->publishMessage('Message - ' . $execution_id, 'test-rpc', null, $header);

        sleep(1);

        $message = $subscriberService->getSingleMessage('test-rpc');

        $this->assertNotNull($message, 'Message is missing');

        echo "(1) Message received: " . $message->body . "\n";
        $subscriberService->messageAcknowledge($message);
        $a_properties = $message->get_properties();
        $this->assertTrue(isset($a_properties['reply_to']) && isset($a_properties['correlation_id']), 'Missing properties');
        $reply_queue = $a_properties['reply_to'];
        $this->assertEquals($message_id, $a_properties['correlation_id'], 'Bad correlation_id');
        $reply_header = [
            'correlation_id' => $a_properties['correlation_id']
        ];
        $subscriberService->publishMessage('Reply to Message - ' . $execution_id, $reply_queue, null, $reply_header);

        $message_reply = $publisherService->getSingleMessage($reply_queue);

        if ($message_reply) {
            echo "(2) Message received: " . $message_reply->body . "\n";
            $a_properties = $message_reply->get_properties();
            $this->assertTrue(isset($a_properties['correlation_id']), 'Missing properties in reply');
            $this->assertEquals($message_id, $a_properties['correlation_id'], 'Bad correlation_id');
            $publisherService->messageAcknowledge($message_reply);
        }

        $publisherService->close();
        $subscriberService->close();
    }
}
