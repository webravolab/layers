<?php

namespace Webravo\Persistence\Eloquent\Store;

use Webravo\Infrastructure\Repository\JobQueueInterface;
use Webravo\Infrastructure\Service\GuidServiceInterface;
use Webravo\Infrastructure\Library\Configuration;
use Webravo\Infrastructure\Library\DependencyBuilder;
use Webravo\Persistence\Eloquent\DataTable\JobDataTable;
use Webravo\Persistence\Eloquent\Hydrators\JobHydrator;

use DB;
use Datetime;
use DateInterval;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EloquentJobStore implements JobQueueInterface {

    private $guidService;
    private $loggerService;
    private $prefix;
    private $last_cleanup_by_queue = [];
    // Eloquent models names to use
    private $jobsQueueModel;
    private $jobsModel;

    public function __construct()
    {
        $this->guidService = DependencyBuilder::resolve('Webravo\Infrastructure\Service\GuidServiceInterface');
        $this->loggerService = DependencyBuilder::resolve('Psr\Log\LoggerInterface');
        $this->prefix = Configuration::get('QUEUE_PREFIX', null, 'no-prefix');
        // Inject Eloquent models names to use (overridable by configuration)
        $this->jobsQueueModel = Configuration::get('JOBS_QUEUE_ELOQUENT_MODEL', null, 'App\JobsQueue');
        $this->jobsModel = Configuration::get('JOBS_ELOQUENT_MODEL', null, 'App\Jobs');
        if (!class_exists($this->jobsQueueModel) || !class_exists($this->jobsModel)) {
            throw new \Exception('[EloquenJobStore] Invalid queue models: '  . $this->jobsQueueModel . ' / ' . $this->jobsModel);
        }
    }

    public function createQueue(string $queueName, string $channelName = '', string $strategy = '', string $routing_key = ''): int
    {
        $prefixedQueueName = $queueName . '-' . $this->prefix;
        $o_queue = $this->jobsQueueModel::firstOrNew(
            [
                'queue_name' => $prefixedQueueName
            ],
            [
                'channel' => $channelName,
                'strategy' => $strategy,
                'status' => 'ACTIVE',
                'guid' => $this->guidService->Generate()->getValue(),
                'routing_key' => $routing_key,
                'created_at' => new DateTime(),
                'last_run_at' => new DateTime()
            ]
        );
        $o_queue->status = 'ACTIVE';
        $o_queue->channel = $channelName;
        $o_queue->strategy = $strategy;
        $o_queue->routing_key = $routing_key;
        $o_queue->last_run_at = new DateTime();
        $o_queue->save();

        return $this->getQueuedJobsNumber($queueName);
    }

    public function bindQueue(string $queueName, string $channel = null)
    {
        $prefixedQueueName = $queueName . '-' . $this->prefix;
        $this->jobsQueueModel::where('queue_name', $prefixedQueueName)
            ->where('channel',$channel)
            ->where('status', '<>', 'DELETED')
            ->update([
                'status' => 'BIND'
            ]);
    }

    public function unbindQueue(string $queueName, string $channel = null)
    {
        $prefixedQueueName = $queueName . '-' . $this->prefix;
        $this->jobsQueueModel::where('queue_name', $prefixedQueueName)
            ->where('channel',$channel)
            ->where('status', 'BIND')
            ->update([
                'status' => 'UNBIND'
            ]);
    }

    public function Append($payload, $channel = null, $bindingKey = null, array $header = [])
    {
        $hydrator = new JobHydrator();

        // Check whether the channel is an exchange or a queue
        $c_queues = $this->jobsQueueModel::where('channel', $channel)
            ->where('status', 'BIND')
            ->get();
        if ($c_queues->count() == 0) {
            $queueName = $channel . '-' . $this->prefix;
            $c_queues = $this->jobsQueueModel::where('queue_name', $queueName)
                ->whereIn('status', ['ACTIVE','BIND'])
                ->get();
            if ($c_queues && $c_queues->count() == 1) {
                $o_queue = $c_queues->first();
                if (empty($o_queue->strategy)) {
                    // It's not an exchange ... queue the job using the channel as queue name
                    $job = new JobDataTable($hydrator);
                    $job->setChannel($queueName);
                    $job->setHeader($header);
                    $job->persist($payload);
                    // Update total # of messages sent to this queue
                    $this->jobsQueueModel::where('queue_name', $queueName)->update(['messages_total' => DB::raw('messages_total +1')]);
                    // Clean old ACK + NACK messages
                    $this->cleanup($queueName);
                }
                else {
                    // No queues BINDed to the exchange channel ... simply discard message
                }
            }
            else {
                throw new \Exception('[EloquentJobStore][Append] Invalid Channel/Queue: ' . $channel);
            }
        }
        else {
            // It's an exchange ... queue the jobs in all subscribed queues
            foreach ($c_queues as $o_queue) {
                $queueName = $o_queue->queue_name;
                $routing_key = $o_queue->routing_key;
                $is_delivered = false;
                switch ($o_queue->strategy) {
                    case '':
                    case 'fanout':
                        // Empty strategy or Fanout ... just queue to all subscribed queues
                        $job = new JobDataTable($hydrator);
                        $job->setChannel($queueName);
                        $job->setHeader($header);
                        $job->persist($payload);
                        $is_delivered = true;
                        break;
                    case 'direct':
                        // Direct strategy ... publish to queues with the same routing_key of the message binding
                        if ($routing_key == $bindingKey) {
                            $job = new JobDataTable($hydrator);
                            $job->setChannel($queueName);
                            $job->setHeader($header);
                            $job->persist($payload);
                            $is_delivered = true;
                        }
                        break;
                    case 'topic':
                        // Topic strategy ... publish to queues matching the binding key of the message
                        $match = '/' . str_replace('*', '(.+)', $routing_key) . '/';
                        if (preg_match($match, $bindingKey)) {
                            $job = new JobDataTable($hydrator);
                            $job->setChannel($queueName);
                            $job->setHeader($header);
                            $job->persist($payload);
                            $is_delivered = true;
                        }
                        break;
                }
                if ($is_delivered) {
                    // Update total # of messages sent to this queue
                    $this->jobsQueueModel::where('id', $o_queue->id)->update(['messages_total' => DB::raw('messages_total +1')]);
                    // Clean old ACK + NACK messages
                    $this->cleanup($queueName);
                }
            }
        }
    }

    public function getQueuedJobsNumber($channel): int
    {
        $channel = $channel . '-' . $this->prefix;
        $n_jobs = $this->jobsModel::where('channel',$channel)
            ->where('status','QUEUED')
            ->where('delivered_token', null)
            ->count();
        return $n_jobs;
    }

    public function AllQueuedJobs($channel): array
    {
        $c_jobs = $this->jobsModel::where('channel',$channel)
            ->where('status','QUEUED')
            ->where('delivered_token', null)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $jobHydrator = new JobHydrator();

        $jobs = Array();
        foreach($c_jobs as $o_job) {
            $jobs[] = $jobHydrator->Hydrate($o_job);
        }

        return $jobs;
    }

    public function nextQueuedJob($channel): ?JobDataTable
    {
        $guid = $this->guidService->Generate()->getValue();
        $channel = $channel . '-' . $this->prefix;

        try {
            $result = $this->jobsModel::where('channel', $channel)
                ->where('status', 'QUEUED')
                ->where('delivered_token', null)
                ->orderBy('created_at')
                ->orderBy('id')
                ->firstOrFail()
                ->update(['status' => 'DELIVERED', 'delivered_token' => $guid, 'delivered_at' => new Datetime()]);
        }
        catch (ModelNotFoundException $e) {
            return null;
        }

        if ($result === false) {
            return null;
        }

        $o_job = $this->jobsModel::where('channel',$channel)
            ->where('status','DELIVERED')
            ->where('delivered_token', $guid)
            ->first();

        if (!$o_job) {
            return null;
        }

        $jobHydrator = new JobHydrator();

        $job = $jobHydrator->Hydrate($o_job);

        return $job;
    }

    public function AcknowledgeJobByGuid($guid)
    {
        $this->jobsModel::where('guid',$guid)
            ->first()
            ->update(['status' => 'ACK']);
    }

    public function NotAcknowledgeJobByGuid($guid)
    {
        $this->jobsModel::where('guid',$guid)
            ->first()
            ->update(['status' => 'NACK']);
    }

    public function deleteQueue(string $queueName, bool $purge = false)
    {
        $queueName = $queueName . '-' . $this->prefix;

        if ($purge) {
            // Set alla QUEUED messages as NACK
            $this->jobsModel::where('channel', $queueName)
                ->where('status', 'QUEUED')
                ->update([
                    'status' => 'NACK'
                ]);
        }
        // Set Queue as DELETED
        $this->jobsQueueModel::where('queue_name', $queueName)
            ->where('status', '<>', 'DELETED')
            ->update([
                'status' => 'DELETED'
            ]);
    }

    /**
     * Clear all ACK messages older that 1 day
     * Clear all NACK messages older than 7 days
     * @param string $queueNameWithPrefix              prefixed queue name
     */
    public function cleanup(string $queueNameWithPrefix)
    {
        try {
            if (isset($this->last_cleanup_by_queue[$queueNameWithPrefix])) {
                // Clear every 15 minutes 
                if ((time()-$this->last_cleanup_by_queue[$queueNameWithPrefix]) < (60 * 15)) {
                    return;
                }
            }
            $this->last_cleanup_by_queue[$queueNameWithPrefix] = time();    // save last cleanup time x queue

            $today = new Datetime();
            $one_day_date = $today->sub(new DateInterval('P1D'))->format('Y-m-d H:i:s');
            $seven_days_date = $today->sub(new DateInterval('P7D'))->format('Y-m-d H:i:s');

            $ack_deleted = $this->jobsModel::where('channel', $queueNameWithPrefix)
                ->where('status', 'ACK')
                ->where('delivered_at', '<', $one_day_date)
                ->delete();

            $this->loggerService->debug('[EloquentJobStore][cleanup]: queue ' . $queueNameWithPrefix . ' - deleted ' . $ack_deleted . " ACK messages before " . $one_day_date);

            $nack_deleted = $this->jobsModel::where('channel', $queueNameWithPrefix)
                ->where('status', 'NACK')
                ->where('delivered_at', '<', $seven_days_date)
                ->delete();

            $this->loggerService->debug('[EloquentJobStore][cleanup]: queue ' . $queueNameWithPrefix . ' - deleted ' . $nack_deleted . " NACK messages before " . $seven_days_date);
        }
        catch (\Exception $e) {
            $this->loggerService->error('[EloquentJobStore][cleanup]: Error deleting old messages from queue ' . $queueNameWithPrefix . ': ' . $e->getMessage());
        }
    }

}