<?php
namespace Webravo\Infrastructure\Service;

interface QueueServiceInterface
{
    public function setDefaultQueue(string $queueName);

    public function getDefaultQueue();

    public function createChannel(string $strategy, string $name, string $bindingKey = null);

    public function deleteChannel(string $name);

    public function getDefaultStrategy();

    public function getDefaultChannelName();

    public function createQueue(string $queueName): int;


    public function subscribeQueue(string $queueName, string $exchangeName = null, string $bindingKey = null);

    public function unsubscribeQueue(string $queueName, string $exchangeName = null, string $bindingKey = null);

    public function purgeQueue(string $queueName): void;

    public function publishMessage($message, $queueName = null, $bindingKey = null, array $header = []): ?string;

    public function processSingleMessage($queueName, $callback);

    public function getSingleMessage($queueName = null);

    public function waitMessages($queueName, $callback);

    public function messageAcknowledge($message);

    public function messageNotAcknowledge($message);
    
    public function close();

    public function delete();
}

