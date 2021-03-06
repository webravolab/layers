<?php
namespace Webravo\Persistence\Eloquent\DataTable;

use Webravo\Infrastructure\Library\Configuration;
use Webravo\Infrastructure\Library\DependencyBuilder;
use Webravo\Infrastructure\Repository\HydratorInterface;
use Webravo\Persistence\Repository\AbstractDataTable;
use Webravo\Persistence\Eloquent\Hydrators\JobHydrator;

use \DateTime;

class JobDataTable extends AbstractDataTable {

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

    public function __construct(HydratorInterface $hydrator)
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
    }

    public static function buildFromArray(array $data): JobDataTable
    {
        $job = new static(new JobHydrator());
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

    public static function getByGuid($guid)
    {
        // TODO: Implement getByGuid() method.
        throw(new Exception('Unimplemented'));
    }

    public function persist($payload) {
        if ($this->jobsModel) {
            if (empty($this->created_at)) {
                $this->created_at = new DateTime();
            }
            if (empty($this->status)) {
                $this->status = 'QUEUED';
            }
            $this->setPayload($payload);

            // Build array data
            $data = $this->hydrator->Extract($this);

            // Create Eloquent Job
            $o_command = $this->jobsModel::create($data);
        }
    }

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

}