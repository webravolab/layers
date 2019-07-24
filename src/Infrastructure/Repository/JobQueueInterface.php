<?php

namespace Webravo\Infrastructure\Repository;

use Webravo\Persistence\Eloquent\DataTable\JobDataTable;

/**
 * JobQueue is a generic Queue to be used for both Events / Commands
 *
 * Interface JobQueueInterface
 * @package Webravo\Infrastructure\Repository
 */
interface JobQueueInterface {

    public function createQueue(string $queueName, string $channelName = '', string $strategy = '', string $routing_key = ''): int;

    public function getQueuedJobsNumber($channel): int;

    public function append($payload, $channel = null, $bindingKey = null, array $header = []);

    public function allQueuedJobs($channel): array;

    public function nextQueuedJob($channel): ?JobDataTable;

    public function acknowledgeJobByGuid($guid);

    public function notAcknowledgeJobByGuid($guid);

    public function deleteQueue(string $queueName, bool $purge = false);

    public function bindQueue(string $queueName, string $channel = null);

    public function unbindQueue(string $queueName, string $channel = null);

    public function purgeQueue(string $queueName): void;
    
    public function cleanup(string $queueNamePrefixed);

    public function deleteChannel(string $channelName);
    
}