<?php
namespace Webravo\Persistence\Eloquent\DataTable;

use Webravo\Common\Entity\DataStoreEventEntity;
use Webravo\Infrastructure\Library\Configuration;
use Webravo\Common\Contracts\HydratorInterface;
use Webravo\Common\Contracts\StoreInterface;
use Webravo\Persistence\Eloquent\Hydrators\EventHydrator;
use Webravo\Persistence\Eloquent\Hydrators\JobHydrator;
use Webravo\Persistence\Eloquent\DataTable\AbstractEloquentStore;
use Webravo\Common\Entity\AbstractEntity;

class EventDataTable extends AbstractEloquentStore implements StoreInterface {

    // Eloquent models names to use
    private $eventsModel;

    protected $id;
    protected $type;
    protected $occurred_at;
    protected $payload;
    
    public function __construct(HydratorInterface $hydrator)
    {
        parent::__construct($hydrator);
        // Inject Eloquent models names to use (overridable by configuration)
        // TODO don't reload configuration if $this->eventsModel is already set
        $eventsModel = Configuration::get('EVENTS_ELOQUENT_MODEL', null, 'App\Events');
        $this->eventsModel = empty($eventsModel) ? null : $eventsModel;
        if ($this->eventsModel) {
            if (!class_exists($this->eventsModel)) {
                $this->eventsModel = null;
                throw new \Exception('[EventDataTable] Invalid events model: ' . $this->eventsModel);
            }
        }
    }

    public static function buildFromArray(array $data): EventDataTable
    {
        $event = new static(new EventHydrator());
        if (isset($data['id'])) { $event->id = $data['id']; }
        if (isset($data['guid'])) { $event->guid = $data['guid']; }
        if (isset($data['type'])) { $event->type = $data['type']; }
        if (isset($data['occurred_at'])) { $event->occurred_at = $data['occurred_at']; }
        if (isset($data['payload'])) { $event->payload = $data['payload'];}
        return $event;
    }


    public function append(array $a_properties)
    {
        $a_attributes = $this->hydrator->mapEloquent($a_properties);
        // Create Eloquent object
        $o_event = $this->eventsModel::create($a_attributes);
    }

    public function persistEntity(AbstractEntity $entity) {
        if ($this->eventsModel) {
            // Extract data from Event as array to store directly on Eloquent model
            if (method_exists($entity, "toSerializedArray")) {
                // Entity could implement it's own serialization method
                $data = $entity->toSerializedArray();
                // $data = $this->hydrator->mapEloquent($data);
            }
            else {
                $data = $entity->toArray();
                // $data = $this->hydrator->mapEloquent($data);
            }
            // Create Eloquent object
            $this->append($data);
            // $o_event = $this->eventsModel::create($data);
        }
    }

    public function persist($payload) {
        // Cannot implement raw payload store
        throw new \Exception('Unimplemented');
    }

    public function getByGuid(string $guid)
    {
        $o_event = $this->getObjectByGuid($guid);
        if (!is_null($o_event)) {
            // Extract raw data from Eloquent model
            // (de-serialization of payload is handled by hydrator->hydrate)
            return $this->hydrator->hydrateEloquent($o_event);
        }
        return null;
    }

    public function getObjectByGuid(string $guid)
    {
        // $eventData = new static(new EventHydrator());
        $eventsModel = $this->eventsModel;
        if ($eventsModel) {
            return $eventsModel::where('guid', $guid)->first();
        }
        return null;
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

    /**
     * Getters & Setters
     **/

    public function setType($type) {
        $this->type = $type;
    }

    public function getType() {
        return $this->type;
    }

    public function setOccurredAt($occurred_at) {
        $this->occurred_at = $occurred_at;
    }

    public function getOccurred_at() {
        return $this->occurred_at;
    }

    public function setPayload($payload) {
        $this->payload = $payload;
    }

    public function getPayload() {
        return $this->payload;
    }
}