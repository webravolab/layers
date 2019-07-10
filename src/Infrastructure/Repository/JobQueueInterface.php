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

    public function Append($payload, $channel = null, $bindingKey = null, array $header = []);

    public function AllQueuedJobs($channel): array;

    public function nextQueuedJob($channel): ?JobDataTable;

    public function AcknowledgeJobByGuid($guid);

    public function NotAcknowledgeJobByGuid($guid);

    public function deleteQueue(string $queueName, bool $purge = false);

    public function bindQueue(string $queueName, string $channel = null);

    public function unbindQueue(string $queueName, string $channel = null);

    public function purgeQueue(string $queueName): void;
    
    public function cleanup(string $queueNamePrefixed);

    public function deleteChannel(string $channelName);
    
}