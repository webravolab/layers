<?php
namespace Webravo\Persistence\Eloquent\DataTable;

use Webravo\Infrastructure\Library\Configuration;
use Webravo\Infrastructure\Repository\HydratorInterface;
use Webravo\Persistence\Eloquent\Hydrators\EventHydrator;
use Webravo\Persistence\Eloquent\Hydrators\JobHydrator;
use Webravo\Persistence\Repository\AbstractDataTable;

class EventDataTable extends AbstractDataTable {

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
        $eventsModel = Configuration::get('EVENTS_ELOQUENT_MODEL', null, 'App\Events');
        $this->eventsModel = empty($eventsModel) ? null : $eventsModel;
        if ($this->eventsModel) {
            if (!class_exists($this->eventsModel)) {
                throw new \Exception('[EventDataTable] Invalid events model: ' . $this->eventsModel);
                $this->eventsModel = null;
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

    public static function getByGuid($guid)
    {
        $eventData = new static(new EventHydrator());
        $eventsModel = $eventData->eventsModel;
        if ($eventsModel) {
            $o_event = $eventsModel::where('guid', $guid)->first();
            if (!is_null($o_event)) {
                // Extract raw data from Eloquent model
                return $eventData->hydrator->Hydrate($o_event);
            }
        }
        return null;
    }

    public function persist($event) {
        if ($this->eventsModel) {
            // Check parent class
            if (strpos(get_parent_class($event), 'GenericEvent') === false) {
                throw new \Exception('EventDataTable: parameter must be instance of DomainEventInterface');
            }

            // Extract data from Event as array to store directly on Eloquent model
            $data = $this->hydrator->Extract($event);

            // Create Eloquent object
            $o_event = $this->eventsModel::create($data);
        }
    }

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