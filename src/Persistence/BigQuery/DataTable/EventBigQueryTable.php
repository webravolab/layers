<?php
namespace Webravo\Persistence\BigQuery\DataTable;

use Webravo\Common\Contracts\HydratorInterface;
use Webravo\Common\Entity\AbstractEntity;
use Webravo\Common\Contracts\StoreInterface;
use Webravo\Persistence\BigQuery\DataTable\AbstractBigQueryStore;
use Webravo\Infrastructure\Service\BigQueryServiceInterface;

use DateTimeInterface;

class EventBigQueryTable extends AbstractBigQueryStore implements StoreInterface {

    protected $id;
    protected $type;
    protected $occurred_at;
    protected $payload;

    public function __construct(BigQueryServiceInterface $bigQueryService, HydratorInterface $hydrator = null, $entity_name = 'EventEntity', $entity_classname = 'Webravo\Common\Entity\EventEntity') {
        // Inject in AbstractBigQueryStore the default Entity to manage Events
        parent::__construct($bigQueryService, $hydrator, $entity_name, $entity_classname, 'events_table', 'events_dataset');
    }

    // All basic functions are implemented by AbstractBigQueryStore

    // Getters & Setters
    public function setType($type) {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setOccurredAt(DateTimeInterface $occurred_at)
    {
        $this->occurred_at = $occurred_at;
    }

    public function getOccurred_at():DateTimeInterface
    {
        return $this->occurred_at;
    }

    public function setPayload($payload)
    {
        $this->payload = $payload;
    }

    public function getPayload()
    {
        return $this->payload;
    }

}