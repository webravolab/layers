<?php
use Webravo\Persistence\Service\DBQueueService;
use Webravo\Persistence\Eloquent\Store\EloquentJobStore;
use Webravo\Persistence\Eloquent\Store\EloquentEventStore;


class DBQueueServiceTest extends TestCase
{

    public function testEventStore()
    {
        $eventStore = new EloquentEventStore();

        $event = new \tests\events\TestEvent();
        $event->setPayload('test value');
        $guid = $event->getGuid();

        $eventStore->Append($event);

        $retrieved_event = $eventStore->getByGuid($guid);

        $this->assertEquals($event->getPayload(), $retrieved_event->getPayload());
    }

    public function testDBQueueServiceSendReceive()
    {

        $payload = "Hello World!";

        $jobQueueService = new EloquentJobStore();
        $service = new DBQueueService($jobQueueService);
        $service->createQueue('test-queue');
        $service->publishMessage($payload, 'test-queue');
        $service->close();
        echo "Message sent\n";

        $subscriberService = new DBQueueService($jobQueueService);
        $subscriberService->subscribeQueue('test-queue');

        $callback = function($message) use ($subscriberService) {
            echo "Message received: " . $message->body . "\n";
            $this->assertEquals($message->body, 'Hello World!');
            $subscriberService->messageAcknowledge($message);
        };
        $subscriberService->processSingleMessage('test-queue', $callback);
        $subscriberService->close();
    }

    public function testFanoutBindingDBQueueService() {

        $execution_id = (new Datetime())->format('H:i:s');

        $jobQueueService = new EloquentJobStore();
        $publisherService  = new DBQueueService($jobQueueService);
        $publisherService->createChannel('fanout', 'fanout-bind-exchange');

        $subscriberService1 = new DBQueueService($jobQueueService);
        $subscriberService1->createChannel('fanout', 'fanout-bind-exchange');
        $subscriberService1->createQueue('test-bind1');
        $subscriberService1->subscribeQueue('test-bind1', 'fanout-bind-exchange');

        $subscriberService2 = new DBQueueService($jobQueueService);
        $subscriberService2->createChannel('fanout', 'fanout-bind-exchange');
        $subscriberService2->createQueue('test-bind2');
        $subscriberService2->subscribeQueue('test-bind2', 'fanout-bind-exchange');

        $callback = function($message) use ($subscriberService1) {
            echo "(1) Message Fanout-bind received: " . $message->body . "\n";
            $subscriberService1->messageAcknowledge($message);
        };

        $callback2 = function($message) use ($subscriberService2) {
            echo "(2) Message Fanout-bind received: " . $message->body . "\n";
            $subscriberService2->messageAcknowledge($message);
        };

        $publisherService->publishMessage('Test fanout-bind 1 ' . $execution_id, '');

        $this->assertEquals(1, $jobQueueService->getQueuedJobsNumber('test-bind1'), 'Wrong number of messages in queue test-bind');
        $this->assertEquals(1, $jobQueueService->getQueuedJobsNumber('test-bind2'), 'Wrong number of messages in queue test-bind2');

        $subscriberService1->processSingleMessage('test-bind1', $callback);
        $subscriberService2->processSingleMessage('test-bind2', $callback2);

        $this->assertEquals(0, $jobQueueService->getQueuedJobsNumber('test-bind1'), 'Wrong number of messages in queue test-bind1');
        $this->assertEquals(0, $jobQueueService->getQueuedJobsNumber('test-bind2'), 'Wrong number of messages in queue test-bind2');

        // Now unbind subscriber2
        $subscriberService2->unsubscribeQueue('test-bind2', 'fanout-bind-exchange');

        $publisherService->publishMessage('Test fanout 2 ' . $execution_id, '');

        $this->assertEquals(1, $jobQueueService->getQueuedJobsNumber('test-bind1'), 'Wrong number of messages in queue test-bind1');
        $this->assertEquals(0, $jobQueueService->getQueuedJobsNumber('test-bind2'), 'Wrong number of messages in queue test-bind2');

        $subscriberService1->processSingleMessage('test-bind1', $callback);

        $publisherService->close();
        $subscriberService1->close();
        $subscriberService2->close();
    }

    public function testDBQueueServiceRoundRobin() {

        $execution_id = (new Datetime())->format('H:i:s');

        $jobQueueService = new EloquentJobStore();
        $publisherService = new DBQueueService($jobQueueService);

        $subscriberService1 = new DBQueueService($jobQueueService);
        $jobs_waiting = $subscriberService1->createQueue('test-roundrobin');
        echo " n. " . $jobs_waiting . " message in queue (1)\n";

        $this->assertEquals($jobs_waiting,0,'Queue is not empty');

        $subscriberService2 = new DBQueueService($jobQueueService);
        $jobs_waiting = $subscriberService2->createQueue('test-roundrobin');
        echo " n. " . $jobs_waiting . " messages in queue (2)\n";

        $this->assertEquals($jobs_waiting,0,'Queue is not empty');

        for($msg = 1; $msg < 5; $msg++) {

            $publisherService->publishMessage('Message ' . $msg . ' - ' . $execution_id, 'test-roundrobin');

            $message1 = $subscriberService1->getSingleMessage('test-roundrobin');
            $message2 = $subscriberService2->getSingleMessage('test-roundrobin');

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

    public function testTopicDBQueueService() {

        $execution_id = (new Datetime())->format('H:i:s');

        $jobQueueService = new EloquentJobStore();
        $publisherService  = new DBQueueService($jobQueueService);
        $publisherService->createChannel('topic', 'topic-exchange');
        // $publisherService->createQueue('test-topic');

        $subscriberService1 = new DBQueueService($jobQueueService);
        $subscriberService1->createChannel('topic', 'topic-exchange');
        $subscriberService1->createQueue('test-topic1');
        $subscriberService1->subscribeQueue('test-topic1', 'topic-exchange', 'queue1.*');

        $subscriberService2 = new DBQueueService($jobQueueService);
        $subscriberService2->createChannel('topic', 'topic-exchange');
        $subscriberService2->createQueue('test-topic2');
        $subscriberService2->subscribeQueue('test-topic2', 'topic-exchange', 'queue2.*');

        $subscriberService3 = new DBQueueService($jobQueueService);
        $subscriberService3->createChannel('topic', 'topic-exchange');
        $subscriberService3->createQueue('test-topic3');
        $subscriberService3->subscribeQueue('test-topic3', 'topic-exchange', '*.test3');

        $callback = function($message) use ($subscriberService1) {
            echo "(1) Message Topic received: " . $message->body . "\n";
            $subscriberService1->messageAcknowledge($message);
        };

        $callback2 = function($message) use ($subscriberService2) {
            echo "(2) Message Topic received: " . $message->body . "\n";
            $subscriberService2->messageAcknowledge($message);
        };

        $callback3 = function($message) use ($subscriberService3) {
            echo "(3) Message Topic received: " . $message->body . "\n";
            $subscriberService3->messageAcknowledge($message);
        };

        $publisherService->publishMessage('Test topic 1-1 ' . $execution_id, '', 'queue1.test1');
        $publisherService->publishMessage('Test topic 1-2 ' . $execution_id, '', 'queue1.test2');
        $publisherService->publishMessage('Test topic 2-1 ' . $execution_id, '', 'queue2.test1');
        $publisherService->publishMessage('Test topic 2-2 ' . $execution_id, '', 'queue2.test2');
        $publisherService->publishMessage('Test topic 3-3 ' . $execution_id, '', 'queue3.test3');
        $publisherService->publishMessage('Test topic 2-3 ' . $execution_id, '', 'queue2.test3');

        $this->assertEquals(2, $jobQueueService->getQueuedJobsNumber('test-topic1'), 'Wrong number of messages in queue test-topic1');
        $this->assertEquals(3, $jobQueueService->getQueuedJobsNumber('test-topic2'), 'Wrong number of messages in queue test-topic2');
        $this->assertEquals(2, $jobQueueService->getQueuedJobsNumber('test-topic3'), 'Wrong number of messages in queue test-topic3');

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
        $subscriberService3->close();
    }

    public function testDirectDBQueueService() {

        $execution_id = (new Datetime())->format('H:i:s');

        $jobQueueService = new EloquentJobStore();
        $publisherService  = new DBQueueService($jobQueueService);
        $publisherService->createChannel('direct', 'direct-exchange');

        $subscriberService1 = new DBQueueService($jobQueueService);
        $subscriberService1->createChannel('direct', 'direct-exchange');
        $subscriberService1->createQueue('test-direct1');
        $subscriberService1->subscribeQueue('test-direct1', 'direct-exchange', 'queue1');

        $subscriberService2 = new DBQueueService($jobQueueService);
        $subscriberService2->createChannel('direct', 'direct-exchange');
        $subscriberService2->createQueue('test-direct2');
        $subscriberService2->subscribeQueue('test-direct2', 'direct-exchange', 'queue2');

        $callback = function($message) use ($subscriberService1) {
            echo "(1) Message Direct received: " . $message->body . "\n";
            $subscriberService1->messageAcknowledge($message);
        };

        $callback2 = function($message) use ($subscriberService2) {
            echo "(2) Message Direct received: " . $message->body . "\n";
            $subscriberService2->messageAcknowledge($message);
        };

        $publisherService->publishMessage('Test direct 1-1 '.$execution_id, '', 'queue1');
        $publisherService->publishMessage('Test direct 1-2 '.$execution_id, '', 'queue1');
        $publisherService->publishMessage('Test direct 2-1 '.$execution_id, '', 'queue2');
        $publisherService->publishMessage('Test direct 2-2 '.$execution_id, '', 'queue2');

        $this->assertEquals(2, $jobQueueService->getQueuedJobsNumber('test-direct1'), 'Wrong number of messages in queue test-direct1');
        $this->assertEquals(2, $jobQueueService->getQueuedJobsNumber('test-direct2'), 'Wrong number of messages in queue test-direct2');

        $subscriberService1->processSingleMessage('test-direct1', $callback);
        $subscriberService2->processSingleMessage('test-direct2', $callback2);
        $subscriberService2->processSingleMessage('test-direct2', $callback2);
        $subscriberService1->processSingleMessage('test-direct1', $callback);

        $publisherService->close();
        $subscriberService1->close();
        $subscriberService2->close();
    }

    public function testFanoutDBQueueService() {

        $execution_id = (new Datetime())->format('H:i:s');

        $jobQueueService = new EloquentJobStore();
        $publisherService  = new DBQueueService($jobQueueService);
        $publisherService->createChannel('fanout', 'fanout-exchange');

        $subscriberService1 = new DBQueueService($jobQueueService);
        $subscriberService1->createChannel('fanout', 'fanout-exchange');
        $subscriberService1->createQueue('test-fanout1');
        $subscriberService1->subscribeQueue('test-fanout1', 'fanout-exchange');

        $subscriberService2 = new DBQueueService($jobQueueService);
        $subscriberService2->createChannel('fanout', 'fanout-exchange');
        $subscriberService2->createQueue('test-fanout2');
        $subscriberService2->subscribeQueue('test-fanout2', 'fanout-exchange');

        $callback = function($message) use ($subscriberService1) {
            echo "(1) Message Fanout received: " . $message->body . "\n";
            $subscriberService1->messageAcknowledge($message);
        };

        $callback2 = function($message) use ($subscriberService2) {
            echo "(2) Message Fanout received: " . $message->body . "\n";
            $subscriberService2->messageAcknowledge($message);
        };

        $publisherService->publishMessage('Test fanout 1 ' . $execution_id, '');

        $this->assertEquals(1, $jobQueueService->getQueuedJobsNumber('test-fanout1'), 'Wrong number of messages in queue test-fanout1');
        $this->assertEquals(1, $jobQueueService->getQueuedJobsNumber('test-fanout2'), 'Wrong number of messages in queue test-fanout2');

        $subscriberService1->processSingleMessage('test-fanout1', $callback);
        $subscriberService2->processSingleMessage('test-fanout2', $callback2);

        $this->assertEquals(0, $jobQueueService->getQueuedJobsNumber('test-fanout1'), 'Wrong number of messages in queue test-fanout1');
        $this->assertEquals(0, $jobQueueService->getQueuedJobsNumber('test-fanout2'), 'Wrong number of messages in queue test-fanout2');

        $publisherService->publishMessage('Test fanout 2 ' . $execution_id, '');
        $publisherService->publishMessage('Test fanout 3 ' . $execution_id, '');

        $this->assertEquals(2, $jobQueueService->getQueuedJobsNumber('test-fanout1'), 'Wrong number of messages in queue test-fanout1');
        $this->assertEquals(2, $jobQueueService->getQueuedJobsNumber('test-fanout2'), 'Wrong number of messages in queue test-fanout2');

        $subscriberService2->processSingleMessage('test-fanout2', $callback2);
        $subscriberService2->processSingleMessage('test-fanout2', $callback2);
        $subscriberService1->processSingleMessage('test-fanout1', $callback);
        $subscriberService1->processSingleMessage('test-fanout1', $callback);

        $publisherService->close();
        $subscriberService1->close();
        $subscriberService2->close();
    }
}
