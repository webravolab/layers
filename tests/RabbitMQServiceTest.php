<?php

use Webravo\Persistence\Service\RabbitMQService;

class RabbitMQTest extends TestCase
{

    public function testRabbitMQServiceSend() {
        $payload = "Hello World!";

        $service = new RabbitMQService();
        $service->publishMessage($payload, 'test-queue');
        $service->close();
        $this->assertTrue(true);
    }

    public function testRabbitMQServiceReceive() {

        $service = new RabbitMQService();

        $callback = function($message) use ($service) {
            echo "Message received: " . $message->body . "\n";
            $this->assertEquals("Hello World!" , $message->body);
            $service->messageAcknowledge($message);
        };

        $service->processSingleMessage('test-queue', $callback);
        $service->close();
    }

    public function testFanoutBindingRabbitMQService() {

        $execution_id = (new Datetime())->format('H:i:s');

        $publisherService = new RabbitMQService();
        $publisherService->createChannel('fanout', 'fanout-bind-exchange');

        $subscriberService1 = new RabbitMQService();
        $subscriberService1->createChannel('fanout', 'fanout-bind-exchange');
        $subscriberService1->createQueue('test-bind1');
        $subscriberService1->subscribeQueue('test-bind1', 'fanout-bind-exchange');

        $subscriberService2 = new RabbitMQService();
        $subscriberService2->createChannel('fanout', 'fanout-bind-exchange');
        $subscriberService2->createQueue('test-bind2');
        $subscriberService2->subscribeQueue('test-bind2', 'fanout-bind-exchange');

        $publisherService->publishMessage('Test bind 1 ' . $execution_id, '');

        $message11 = $subscriberService1->getSingleMessage('test-bind1');
        $message21 = $subscriberService2->getSingleMessage('test-bind2');

        $this->assertNotNull($message11, 'Message 11 must not be null');
        $this->assertNotNull($message21, 'Message 21 must not be null');

        if ($message11) {
            echo "(bind) Message 11 received: " . $message11->body . "\n";
            $subscriberService1->messageAcknowledge($message11);
        }

        if ($message21) {
            echo "(bind) Message 21 received: " . $message21->body . "\n";
            $subscriberService2->messageAcknowledge($message21);
        }

        // Now unbind subscriber2
        $subscriberService2->unsubscribeQueue('test-bind2', 'fanout-bind-exchange');

        $publisherService->publishMessage('Test bind 2 ' . $execution_id, '');

        $message12 = $subscriberService1->getSingleMessage('test-bind1');
        $message22 = $subscriberService2->getSingleMessage('test-bind2');

        if ($message12) {
            echo "(bind) Message 12 received: " . $message12->body . "\n";
            $subscriberService1->messageAcknowledge($message12);
        }

        $this->assertNotNull($message21, 'Message 21 must not be null');
        $this->assertNull($message22, 'Message 22 MUST be null');


        $subscriberService1->unsubscribeQueue('test-bind1', 'fanout-bind-exchange');
        $publisherService->close();
        $subscriberService1->close();
        $subscriberService2->close();
    }

    public function testFanout2RabbitMQService() {

        $execution_id = (new Datetime())->format('H:i:s');

        $publisherService = new RabbitMQService();
        $publisherService->createChannel('fanout', 'fanoutB-exchange');

        $publisherService->publishMessage('Test fanout2-1 ' . $execution_id, '');

        $subscriberService1 = new RabbitMQService();
        $subscriberService1->createQueue('test-fanoutB');
        $subscriberService1->subscribeQueue('test-fanoutB', 'fanoutB-exchange');

        $callback = function($message) use ($subscriberService1) {
            echo "(1) Message Fanout2 received: " . $message->body . "\n";
            $this->assertNotEmpty($message->body);
            $subscriberService1->messageAcknowledge($message);
        };

        $subscriberService1->processSingleMessage('test-fanoutB', $callback);

        $publisherService->close();
        $subscriberService1->close();
    }

    public function testRabbitMQServiceRoundRobin() {

        $execution_id = (new Datetime())->format('H:i:s');

        $publisherService = new RabbitMQService();

        $subscriberService1 = new RabbitMQService();
        $jobs_waiting = $subscriberService1->createQueue('test-roundrobin');
        echo " n. " . $jobs_waiting . " message in queue (1)\n";

        $this->assertEquals($jobs_waiting,0,'Queue is not empty');

        $subscriberService2 = new RabbitMQService();
        $jobs_waiting = $subscriberService2->createQueue('test-roundrobin');
        echo " n. " . $jobs_waiting . " messages in queue (2)\n";

        $this->assertEquals($jobs_waiting,0,'Queue is not empty');

        for($msg = 1; $msg < 5; $msg++) {

            $publisherService->publishMessage('Message ' . $msg . ' - ' . $execution_id, 'test-roundrobin');

            $message1 = $subscriberService1->getSingleMessage('test-roundrobin');
            $message2 = $subscriberService2->getSingleMessage('test-roundrobin');

            $this->assertNotNull($message1, 'Message 1 must not be null');
            $this->assertTrue((strpos($message1->body, 'Message ' . $msg)!==false), "Bad message $msg reveived " . $message1->body);
            $this->assertNull($message2, 'Message 2 is not null');

            if ($message1) {
                echo "(1) Message received: " . $message1->body . "\n";
                $subscriberService1->messageAcknowledge($message1);
            }

            if ($message2) {
                echo "(2) Message received: " . $message2->body . "\n";
                $subscriberService2->messageAcknowledge($message2);
            }
        }
        $publisherService->close();
        $subscriberService1->close();
        $subscriberService2->close();
    }

    public function testTopicRabbitMQService() {

        $execution_id = (new Datetime())->format('H:i:s');

        $publisherService = new RabbitMQService();
        $publisherService->createChannel('topic', 'topic-exchange');

        $subscriberService1 = new RabbitMQService();
        $subscriberService1->createChannel('topic', 'topic-exchange');
        $subscriberService1->createQueue('test-topic1');
        $subscriberService1->subscribeQueue('test-topic1','topic-exchange', 'queue1.*');

        $subscriberService2 = new RabbitMQService();
        $subscriberService2->createChannel('topic', 'topic-exchange');
        $subscriberService2->createQueue('test-topic2');
        $subscriberService2->subscribeQueue('test-topic2', 'topic-exchange', 'queue2.*');

        $subscriberService3 = new RabbitMQService();
        $subscriberService3->createChannel('topic', 'topic-exchange');
        $subscriberService3->createQueue('test-topic3');
        $subscriberService3->subscribeQueue('test-topic3', 'topic-exchange', '*.test3');

        $callback = function($message) use ($subscriberService1) {
            echo "(1) Message Topic received: " . $message->body . "\n";
            $this->assertNotEmpty($message->body);
            $subscriberService1->messageAcknowledge($message);
        };

        $callback2 = function($message) use ($subscriberService2) {
            echo "(2) Message Topic received: " . $message->body . "\n";
            $this->assertNotEmpty($message->body);
            $subscriberService2->messageAcknowledge($message);
        };

        $callback3 = function($message) use ($subscriberService3) {
            echo "(3) Message Topic received: " . $message->body . "\n";
            $this->assertNotEmpty($message->body);
            $subscriberService3->messageAcknowledge($message);
        };

        $publisherService->publishMessage('Test topic 1-1 ' . $execution_id, '', 'queue1.test1');
        $publisherService->publishMessage('Test topic 1-2 ' . $execution_id, '', 'queue1.test2');
        $publisherService->publishMessage('Test topic 2-1 ' . $execution_id, '', 'queue2.test1');
        $publisherService->publishMessage('Test topic 2-2 ' . $execution_id, '', 'queue2.test2');
        $publisherService->publishMessage('Test topic 3-3 ' . $execution_id, '', 'queue3.test3');
        $publisherService->publishMessage('Test topic 2-3 ' . $execution_id, '', 'queue2.test3');

        $subscriberService1->processSingleMessage('test-topic1', $callback);
        $subscriberService2->processSingleMessage('test-topic2', $callback2);
        $subscriberService2->processSingleMessage('test-topic2', $callback2);
        $subscriberService1->processSingleMessage('test-topic1', $callback);
        $subscriberService3->processSingleMessage('test-topic3', $callback3);
        $subscriberService2->processSingleMessage('test-topic2', $callback2);
        $subscriberService3->processSingleMessage('test-topic3', $callback3);

        $publisherService->close();
        $subscriberService1->close();
        $subscriberService2->close();
    }
    
    public function testDirectRabbitMQService() {

        $execution_id = (new Datetime())->format('H:i:s');

        $publisherService = new RabbitMQService();
        $publisherService->createChannel('direct', 'direct-exchange');

        $subscriberService1 = new RabbitMQService();
        // $subscriberService1->createChannel('direct', 'direct-exchange', 'queue1');
        $subscriberService1->createQueue('test-direct1');
        $subscriberService1->subscribeQueue('test-direct1','direct-exchange', 'queue1');

        $subscriberService2 = new RabbitMQService();
        // $subscriberService2->createChannel('direct', 'direct-exchange', 'queue2');
        $subscriberService2->createQueue('test-direct2');
        $subscriberService2->subscribeQueue('test-direct2','direct-exchange', 'queue2');

        $callback = function($message) use ($subscriberService1) {
            echo "(1) Message Direct received: " . $message->body . "\n";
            $this->assertNotEmpty($message->body);
            $subscriberService1->messageAcknowledge($message);
        };

        $callback2 = function($message) use ($subscriberService2) {
            echo "(2) Message Direct received: " . $message->body . "\n";
            $this->assertNotEmpty($message->body);
            $subscriberService2->messageAcknowledge($message);
        };

        $publisherService->publishMessage('Test direct 1-1 '.$execution_id, '', 'queue1');
        $publisherService->publishMessage('Test direct 1-2 '.$execution_id, '', 'queue1');
        $publisherService->publishMessage('Test direct 2-1 '.$execution_id, '', 'queue2');
        $publisherService->publishMessage('Test direct 2-2 '.$execution_id, '', 'queue2');

        $subscriberService1->processSingleMessage('test-direct1', $callback);
        $subscriberService2->processSingleMessage('test-direct2', $callback2);
        $subscriberService2->processSingleMessage('test-direct2', $callback2);
        $subscriberService1->processSingleMessage('test-direct1', $callback);

        $publisherService->close();
        $subscriberService1->close();
        $subscriberService2->close();
    }

    public function testFanout1RabbitMQService() {

        $execution_id = (new Datetime())->format('H:i:s');

        $publisherService = new RabbitMQService();
        $publisherService->createChannel('fanout', 'fanout-exchange');

        $subscriberService1 = new RabbitMQService();
        // $subscriberService1->createChannel('fanout', 'fanout-exchange');
        $subscriberService1->createQueue('test-fanout1');
        $subscriberService1->subscribeQueue('test-fanout1', 'fanout-exchange');

        $subscriberService2 = new RabbitMQService();
        // $subscriberService2->createChannel('fanout', 'fanout-exchange');
        $subscriberService2->createQueue('test-fanout2');
        $subscriberService2->subscribeQueue('test-fanout2', 'fanout-exchange');

        $callback = function($message) use ($subscriberService1) {
            echo "(1) Message Fanout received: " . $message->body . "\n";
            $this->assertNotEmpty($message->body);
            $subscriberService1->messageAcknowledge($message);
        };

        $callback2 = function($message) use ($subscriberService2) {
            echo "(2) Message Fanout received: " . $message->body . "\n";
            $this->assertNotEmpty($message->body);
            $subscriberService2->messageAcknowledge($message);
        };

        $publisherService->publishMessage('Test fanout 1 ' . $execution_id, '');

        $subscriberService1->processSingleMessage('test-fanout1', $callback);
        $subscriberService2->processSingleMessage('test-fanout2', $callback2);

        $publisherService->publishMessage('Test fanout 2 ' . $execution_id, '');
        $publisherService->publishMessage('Test fanout 3 ' . $execution_id, '');

        $subscriberService2->processSingleMessage('test-fanout2', $callback2);
        $subscriberService2->processSingleMessage('test-fanout2', $callback2);
        $subscriberService1->processSingleMessage('test-fanout1', $callback);
        $subscriberService1->processSingleMessage('test-fanout1', $callback);

        $publisherService->close();
        $subscriberService1->close();
        $subscriberService2->close();
    }

}
