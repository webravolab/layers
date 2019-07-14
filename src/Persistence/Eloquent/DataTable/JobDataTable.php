<?php
namespace Webravo\Persistence\Eloquent\DataTable;

use Webravo\Common\Entity\AbstractEntity;
use Webravo\Infrastructure\Library\Configuration;
use Webravo\Infrastructure\Library\DependencyBuilder;
use Webravo\Common\Contracts\HydratorInterface;
use Webravo\Common\Contracts\StoreInterface;
use Webravo\Persistence\Eloquent\DataTable\AbstractEloquentStore;
use Webravo\Persistence\Eloquent\Hydrators\JobHydrator;

use DateTime;

class JobDataTable
{
    public $body;           // Compatibility with AMQPMessage

    protected $id;
    protected $jobName;
    protected $channel;
    protected $status;
    protected $created_at;
    protected $delivered_at;
    protected $delivered_token;
    protected $payload;
    protected $header;

    // Eloquent models names to use
    private $jobsModel;

    public function __construct(HydratorInterface $hydrator = null)
    {
        parent::__construct($hydrator);
        // Inject Eloquent models names to use (overridable by configuration)
        $jobsModel = Configuration::get('JOBS_ELOQUENT_MODEL', null, 'App\Jobs');
        $this->jobsModel = empty($jobsModel) ? null : $jobsModel;
        if ($this->jobsModel) {
            if (!class_exists($this->jobsModel)) {
                throw new \Exception('[JobDataTable] Invalid job model: ' . $this->jobsModel);
                $this->jobsModel = null;
            }
        }
        // Set default values
        $this->created_at = new DateTime();
        $this->status = 'QUEUED';
    }

    public static function buildFromArray(array $data): JobDataTable
    {
        $job = new static();
        if (isset($data['id'])) { $job->id = $data['id']; }
        if (isset($data['guid'])) { $job->guid = $data['guid']; }
        if (isset($data['name'])) { $job->jobName = $data['name']; }
        if (isset($data['channel'])) { $job->channel = $data['channel']; }
        if (isset($data['status'])) { $job->status = $data['status']; }
        if (isset($data['created_at'])) { $job->created_at = $data['created_at']; }
        if (isset($data['delivered_at'])) { $job->delivered_at = $data['delivered_at']; }
        if (isset($data['delivered_token'])) { $job->delivered_token = $data['delivered_token']; }
        if (isset($data['header'])) { $job->header = $data['header']; }
        if (isset($data['payload'])) {
            $job->payload = $data['payload'];
            $job->body = $data['payload'];
        }
        return $job;
    }

    public function getByGuid(string $guid)
    {
        // TODO: Implement getByGuid() method.
        throw(new Exception('Unimplemented'));
    }

    public function getObjectByGuid(string $guid)
    {
        // TODO: Implement getObjectByGuid() method.
        throw(new Exception('Unimplemented'));
    }

    public function append(array $data) {
        // TODO: Implement append() method.
        /*
        if ($this->jobsModel) {
            if (empty($this->created_at)) {
                $this->created_at = new DateTime();
            }
            if (empty($this->status)) {
                $this->status = 'QUEUED';
            }

            $data = $this->toArray();

            // Create Eloquent Job
            $o_job = $this->jobsModel::create($data);
        }
        */
    }

    public function update(array $data)
    {
        // TODO: Implement update() method.
    }

    public function delete(array $data)
    {
        // TODO: Implement delete() method.
    }

    public function deleteByGuid(string $guid)
    {
        // TODO: Implement deleteByGuid() method.
    }

    // Getters & Setters

    public function setName($name) {
        $this->jobName = $name;
    }

    public function getName() {
        return $this->jobName;
    }

    public function setChannel($channel) {
        $this->channel = $channel;
    }

    public function getChannel() {
        return $this->channel;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setCreatedAt($created_at) {
        $this->created_at = $created_at;
    }

    public function getCreatedAt() {
        return $this->created_at;
    }

    public function setDeliveredAt($delivered_at) {
        $this->delivered_at = $delivered_at;
    }

    public function getDeliveredAt() {
        return $this->delivered_at;
    }

    public function setDeliveredToken($token) {
        $this->delivered_token = $token;
    }

    public function getDeliveredToken() {
        return $this->delivered_token;
    }

    public function setPayload($payload) {
        $this->payload = $payload;
        $this->body = $payload;
    }

    public function getPayload() {
        return $this->payload;
    }

    public function setHeader($header) {
        if (is_array($header)) {
            $this->header = json_encode($header);
        }
        else {
            $this->header = $header;
        }
    }

    /**
     * Return header decoded from Json to Array
     *
     * @return array
     */
    public function getHeader() {
        return json_decode($this->header, true);
    }

    /**
     * Return raw header (Json not decoded)
     * @return string
     */
    public function getRawHeader() {
        return $this->header;
    }

    /**
     * Return header properties array
     * (Needed for compatibility with AMQPMessage)
     */
    public function get_properties() {
        return $this->getHeader();
    }

    public function toArray()
    {
        $data = [
            'guid' => $this->getGuid(),
            'name' => $this->getName(),
            'channel' => $this->getChannel(),
            'status' => $this->getStatus(),
            'created_at' => $this->getCreatedAt(),
            'delivered_at' => $this->getDeliveredAt(),
            'payload' => $this->getPayload(),
            'header' => $this->getRawHeader()
        ];
        return $data;
    }
}