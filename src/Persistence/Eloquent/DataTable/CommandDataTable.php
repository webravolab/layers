<?php
namespace Webravo\Persistence\Eloquent\DataTable;

use Webravo\Common\Entity\AbstractEntity;
use Webravo\Infrastructure\Library\Configuration;
use Webravo\Infrastructure\Library\DependencyBuilder;
use Webravo\Infrastructure\Repository\HydratorInterface;
use Webravo\Common\Contracts\StoreInterface;
use Webravo\Persistence\Eloquent\Hydrators\CommandHydrator;
use Webravo\Persistence\Repository\AbstractEloquentStore;
use Webravo\Persistence\Eloquent\Hydrators\JobHydrator;

use DateTime;

class CommandDataTable extends AbstractEloquentStore implements StoreInterface {

    public $body;           // Compatibility with AMQPMessage

    protected $id;
    protected $jobName;
    protected $channel;
    // protected $status;
    protected $created_at;
    // protected $delivered_at;
    // protected $delivered_token;
    protected $payload;
    protected $header;

    // Eloquent models names to use
    private $commandModel;

    public function __construct(HydratorInterface $hydrator)
    {
        parent::__construct($hydrator);
        // Inject Eloquent models names to use (overridable by configuration)
        $commandModel = Configuration::get('COMMAND_ELOQUENT_MODEL', null, 'App\Commands');
        $this->commandModel = empty($commandModel) ? null : $commandModel;
        if ($this->commandModel) {
            if (!class_exists($this->commandModel)) {
                throw new \Exception('[CommandDataTable] Invalid command model: ' . $this->commandModel);
                $this->commandModel = null;
            }
        }
        $this->created_at = new DateTime();
    }

    public static function buildFromArray(array $data): CommandDataTable
    {
        $command = new static(new CommandHydrator());
        if (isset($data['id'])) { $command->id = $data['id']; }
        if (isset($data['guid'])) { $command->guid = $data['guid']; }
        if (isset($data['name'])) { $command->jobName = $data['name']; }
        if (isset($data['channel'])) { $command->channel = $data['channel']; }
        // if (isset($data['status'])) { $command->status = $data['status']; }
        if (isset($data['created_at'])) { $command->created_at = $data['created_at']; }
        // if (isset($data['delivered_at'])) { $command->delivered_at = $data['delivered_at']; }
        // if (isset($data['delivered_token'])) { $command->delivered_token = $data['delivered_token']; }
        if (isset($data['header'])) { $command->header = $data['header']; }
        if (isset($data['payload'])) {
            $command->payload = $data['payload'];
        }
        return $command;
    }

    public function getByGuid($guid)
    {
        $o_command = $this->getObjectByGuid($guid);
        if (!is_null($o_command)) {
            // Extract raw data from Eloquent model
            // (de-serialization of payload is handled by hydrator->hydrate)
            return $this->hydrator->hydrate($o_command);
        }
        return null;
    }

    public function getObjectByGuid($guid)
    {
        if ($this->commandModel) {
            return $this->commandModel::where('guid', $guid)->first();
        }
        return null;
    }

    public function persistEntity(AbstractEntity $entity) {
        if ($this->commandModel) {
            // Extract data from Command as array to store directly on Eloquent model
            if (method_exists($entity, "toSerializedArray")) {
                // Entity could implement it's own serialization method
                $data = $entity->toSerializedArray();
            }
            else {
                $data = $entity->toArray();
            }
            // Add creation date
            $data['created_at'] = $this->getCreatedAt();
            // Convert array to Eloquent model data structure
            $data = $this->hydrator->map($data);
            // Store Eloquent object
            $o_command = $this->commandModel::create($data);
        }
    }

    public function persist($payload) {
        if ($this->commandModel) {
            if (empty($this->created_at)) {
                $this->created_at = new DateTime();
            }
            if (empty($this->status)) {
                $this->status = 'QUEUED';
            }
            $this->setPayload($payload);

            // Build array data
            $data = $this->hydrator->Extract($this);

            // Create Eloquent Command
            $o_command = $this->commandModel::create($data);
        }
    }

    public function update(AbstractEntity $entity)
    {
        // TODO: Implement update() method.
    }

    public function delete(AbstractEntity $entity)
    {
        // TODO: Implement delete() method.
    }

    public function deleteByGuid($guid)
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

    /*
    public function setStatus($status) {
        $this->status = $status;
    }

    public function getStatus() {
        return $this->status;
    }
    */

    public function setCreatedAt($created_at) {
        $this->created_at = $created_at;
    }

    public function getCreatedAt() {
        return $this->created_at;
    }

    /*
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
    */

    public function setPayload($payload) {
        $this->payload = $payload;
        // $this->body = $payload;
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

}