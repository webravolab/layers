<?php

namespace Webravo\Persistence\Service;

use Webravo\Infrastructure\Repository\CommandRepositoryInterface;
use Webravo\Infrastructure\Repository\JobQueueInterface;
use Webravo\Infrastructure\Service\QueueServiceInterface;
use Webravo\Infrastructure\Library\Configuration;

class DBQueueService implements QueueServiceInterface {

    private $channel;
    private $defaultQueue = 'trash-queue';  // Default: go to trash
    private $queueStore;
    private $defaultStrategy = '';          // Default: named queue
    private $channelName = '';              // Default: straight queue
    private $bindingKey = '';               // Default: no routing binding key
    private $subscribedQueue = '';          // Default: no subscription

    private $queues = array();              // Keep track of open queues

    public function __construct(JobQueueInterface $queueStore) {
        $this->defaultQueue = Configuration::get('DEFAULT_QUEUE', null, 'trash-queue');
        $this->queueStore = $queueStore;
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

    public function deleteChannel(string $exchange_name)
    {
        $this->queueStore->deleteChannel($this->channelName);
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
        $n_jobs = $this->queueStore->createQueue($queueName, $this->channelName, $this->defaultStrategy, $this->bindingKey);
        return $n_jobs;
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
        $this->queueStore->bindQueue($queueName, $channelName, $bindingKey);
    }

    public function unsubscribeQueue(string $queueName, string $channelName = null, string $bindingKey = null)
    {
        if (!empty($channelName)) {
            $this->channelName = $channelName;
        }
        if (!empty($bindingKey)) {
            $this->bindingKey = $bindingKey;
        }
        $this->subscribedQueue = null;
        $this->queueStore->unbindQueue($queueName, $channelName, $bindingKey);
        $this->queueStore->cleanup($queueName);
    }

    public function purgeQueue(string $queueName): void
    {
        $this->queueStore->purgeQueue($queueName);
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
            $this->queueStore->append($message, $this->defaultQueue, $bindingKey, $header);
        }
        else {
            $this->queueStore->append($message, $this->channelName, $bindingKey, $header);
        }
        return null;    // TODO
    }

    public function waitMessages($queueName, $callback)
    {
        if (!empty($queueName)) {
            $this->defaultQueue = $queueName;
            $this->queues[$queueName] = true;
        }
        while (true) {
            $job = $this->queueStore->nextQueuedJob($this->defaultQueue);
            if ($job) {
                call_user_func($callback, $job);
            }
        }
    }

    public function processSingleMessage($queueName, $callback)
    {
        $message = $this->getSingleMessage($queueName);
        if ($message) {
            call_user_func($callback, $message);
        }
    }

    public function getSingleMessage($queueName = null) {

        if (!empty($queueName)) {
            $this->defaultQueue = $queueName;
            $this->queues[$queueName] = true;
        }
        $job = $this->queueStore->nextQueuedJob($this->defaultQueue);

        if ($job) {
            return $job;
        }
        return null;
    }

    public function messageAcknowledge($message)
    {
        $guid = $message->getGuid();
        $this->queueStore->acknowledgeJobByGuid($guid);
    }

    public function messageNotAcknowledge($message)
    {
        $guid = $message->getGuid();
        $this->queueStore->notAcknowledgeJobByGuid($guid);
    }
    

    public function close()
    {
        // Nothing to do ...
        // ... Don't delete queues on close
    }
        
    public function delete()
    {
        // Delete all referenced queues
        foreach($this->queues as $queueName => $bool) {
            $this->queueStore->deleteQueue($queueName);
        }
    }
        
}