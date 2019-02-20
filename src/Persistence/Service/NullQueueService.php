<?php

namespace Webravo\Persistence\Service;

use Webravo\Infrastructure\Repository\CommandStoreInterface;
use Webravo\Infrastructure\Service\QueueServiceInterface;
use Webravo\Infrastructure\Library\Configuration;

class NullQueueService implements QueueServiceInterface {

    private $channel;
    private $defaultQueue = 'trash-queue';  // Default: go to trash
    private $queueStore = null;
    private $defaultStrategy = '';          // Default: named queue
    private $channelName = '';              // Default: straight queue
    private $bindingKey = '';               // Default: no routing binding key
    private $subscribedQueue = '';          // Default: no subscription

    private $queues = array();              // Keep track of open queues

    public function __construct() {
        // Nothing to do
        // $this->>queueStore = ??
    }

    public function setDefaultQueue(string $queueName)
    {
        $this->defaultQueue = $queueName;
    }

    public function getDefaultQueue()
    {
        if ($this->defaultQueue == 'trash-queue') {
            return null;
        }
        return $this->defaultQueue;
    }

    public function createChannel(string $strategy, string $channelName, string $bindingKey = null)
    {
        switch($strategy) {
            case '':
            case 'direct':
            case 'topic':
            case 'fanout':
                // Save strategy parameters
                $this->channelName = $channelName;
                $this->defaultStrategy = $strategy;
                $this->bindingKey = $bindingKey ?? '';
                break;
            default:
                throw new \Exception('Bad queue strategy: ' . $strategy);
                break;
        }
    }

    public function getDefaultStrategy()
    {
        return $this->defaultStrategy;
    }

    public function getDefaultChannelName()
    {
        return $this->channelName;
    }

    public function createQueue(string $queueName): int
    {
        $this->defaultQueue = $queueName;
        $this->queues[$queueName] = true;
        // DUMMY
        return 0;
    }

    public function subscribeQueue(string $queueName, string $channelName = null, string $bindingKey = null)
    {
        if (!empty($channelName)) {
            $this->channelName = $channelName;
        }
        if (!empty($bindingKey)) {
            $this->bindingKey = $bindingKey;
        }
        $this->subscribedQueue = $queueName;
        $this->createQueue($queueName);
    }

    public function unsubscribeQueue(string $queueName, string $exchangeName = null, string $bindingKey = null)
    {
        // TODO: Implement unsubscribeQueue() method.
    }

    public function publishMessage($message, $queueName = null, $bindingKey = null, array $header = []): ?string
    {
        if (empty($this->channelName) && empty($this->defaultStrategy)) {
            if (empty($queueName) && !empty($this->defaultQueue)) {
                $queueName = $this->defaultQueue;
            }
            $this->createChannel('', $queueName);
        }
        if (!empty($queueName)) {
            $this->defaultQueue = $queueName;
            $this->createQueue($queueName);
            // DUMMY
            // $this->queueStore->Append($message, $this->defaultQueue, $bindingKey, $header);
        }
        else {
            // DUMMY
            // $this->queueStore->Append($message, $this->channelName, $bindingKey, $header);
        }
        return null;    // TODO
    }

    public function waitMessages($queueName, $callback)
    {
        if (!empty($queueName)) {
            $this->defaultQueue = $queueName;
            $this->queues[$queueName] = true;
        }
        $job = null;
        // while (true) {
            // DUMMY
            // $job = $this->queueStore->nextQueuedJob($this->defaultQueue);
            if ($job) {
                call_user_func($callback, $job);
            }
        // }
    }

    public function processSingleMessage($queueName, $callback)
    {
        $message = $this->getSingleMessage($queueName);
        if ($message) {
            call_user_func($callback, $message);
        }
    }

    public function getSingleMessage($queueName) {

        if (!empty($queueName)) {
            $this->defaultQueue = $queueName;
            $this->queues[$queueName] = true;
        }
        // DUMMY
        // $job = $this->queueStore->nextQueuedJob($this->defaultQueue);
        $job = null;

        if ($job) {
            return $job;
        }
        return null;
    }

    public function messageAcknowledge($message)
    {
        // DUMMY
        // $guid = $message->getGuid();
        // $this->queueStore->AcknowledgeJobByGuid($guid);
    }

    public function messageNotAcknowledge($message)
    {
        // TODO: Implement messageNotAcknowledge() method.
    }

    public function delete()
    {
        // TODO: Implement delete() method.
    }

    public function close()
    {
        foreach($this->queues as $queueName => $bool) {
            // DUMMY
            // $this->queueStore->deleteQueue($queueName);
        }
    }

}