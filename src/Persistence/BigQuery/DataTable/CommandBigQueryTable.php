<?php
namespace Webravo\Persistence\BigQuery\DataTable;

use Webravo\Common\Contracts\HydratorInterface;
use Webravo\Common\Contracts\StoreInterface;
use Webravo\Infrastructure\Service\BigQueryServiceInterface;

use DateTimeInterface;

class CommandBigQueryTable extends AbstractBigQueryStore implements StoreInterface {

    protected $id;
    protected $jobName;
    protected $channel;
    protected $payload;
    protected $header;
    protected $created_at;

    public function __construct(BigQueryServiceInterface $bigQueryService, HydratorInterface $hydrator = null, $entity_name = 'CommandEntity', $entity_classname = 'Webravo\Common\Entity\CommandEntity') {
        // Inject in AbstractGdsStore the default Entity to manage Commands
        parent::__construct($bigQueryService, $hydrator, $entity_name, $entity_classname, 'commands_table', 'commands_dataset');

    }

    // All basic functions are implemented by AbstractDataStoreTable

    // Getters & Setters

    public function setName($name) {
        $this->jobName = $name;
    }

    public function getName()
    {
        return $this->jobName;
    }

    public function setChannel($chanel) {
        $this->channel = $chanel;
    }

    public function getChannel()
    {
        return $this->channel;
    }

    public function setCreatedAt(DateTimeInterface $created_at)
    {
        $this->created_at = $created_at;
    }

    public function getCreatedAt():DateTimeInterface
    {
        return $this->created_at;
    }

    public function setHeader($header)
    {
        $this->header = $header;
    }

    public function getHeader()
    {
        return $this->header;
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