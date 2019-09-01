<?php
namespace Webravo\Persistence\Eloquent\DataTable;

use Webravo\Common\Entity\EventEntity;
use Webravo\Infrastructure\Library\Configuration;
use Webravo\Common\Contracts\HydratorInterface;
use Webravo\Common\Contracts\StoreInterface;
use Webravo\Infrastructure\Library\DependencyBuilder;
use Webravo\Persistence\Hydrators\EventStreamHydrator;
use Webravo\Persistence\Eloquent\DataTable\AbstractEloquentStore;
use Webravo\Common\Entity\AbstractEntity;

class AggregateDomainEventDataTable extends AbstractEloquentStore implements StoreInterface {

    // Eloquent models names to use
    private $eventsModel;

    protected $id;
    protected $type;
    protected $occurred_at;
    protected $payload;

    public function __construct(HydratorInterface $hydrator, $aggregate_type) {
        parent::__construct($hydrator);
        // Inject Eloquent models names to use (overridable by configuration)
        $eventsModel = $aggregate_type;
        // Try to locate the model
        if (!class_exists($eventsModel)) {
            // Try prepending a standard prefix
            $prefix = Configuration::get('EVENTSOURCE_ELOQUENT_PREFIX', null, 'App\EventSource');
            $eventsModel = $prefix . $aggregate_type;
            if (!class_exists($eventsModel)) {
                // No model with the name of the aggregate
                $this->eventsModel = null;
                throw(new \Exception('[AggregateDomainEventDataTable] Invalid events model: ' . $eventsModel));
            }
            $this->eventsModel = $eventsModel;
        }
    }

    public function getEventsByAggregateId($aggregate_id): array
    {
        $version = (int) 0;

        // Check for last snapshots
        $o_snapshot = $this->eventsModel::where('aggregate_id', '=', $aggregate_id)
            ->where('event', '=', 'Snapshot')
            ->orderBy('version','desc')
            ->first();
        if ($o_snapshot) {
            $version = (int) $o_snapshot->version;
        }
        // Read events from the last snapshot ot from the beginning
        $c_events = $this->eventsModel::where('aggregate_id', '=', $aggregate_id)
            ->where('version', '>=', $version)
            ->orderBy('version','asc')
            ->get();
        $entities = [];
        foreach ($c_events as $entity) {
            if ($this->hydrator) {
                // Use hydrator if set
                $a_properties = $this->hydrator->hydrateDatastore($entity);
                $entities[] = $a_properties;
            } else {
                // Return raw data
                $entities[] = $entity->attributesToArray();
            }
        }
        return $entities;
    }

    public static function buildFromArray(array $data): AggregateDomainEventDataTable
    {
        $event = new static(new EventStreamHydrator());
        if (isset($data['id'])) { $event->id = $data['id']; }
        if (isset($data['guid'])) { $event->guid = $data['guid']; }
        if (isset($data['type'])) { $event->event_type = $data['type']; }
        if (isset($data['aggregate_type'])) { $event->aggregate_type = $data['aggregate_type']; }
        if (isset($data['aggregate_id'])) { $event->aggregate_id = $data['aggregate_id']; }
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