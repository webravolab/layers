<?php
namespace tests\TestProject\Domain\Repository;

use tests\TestProject\Domain\AggregateRoot\TestTransaction;
use tests\TestProject\Domain\Entity\TestEntity;
use Webravo\Common\Entity\AbstractEntity;
use Webravo\Common\Entity\EntityInterface;
use Webravo\Infrastructure\Library\DependencyBuilder;
use tests\TestProject\Infrastructure\Repository\TestStoreInterface;
use Webravo\Common\Contracts\RepositoryInterface;
use tests\TestProject\Infrastructure\Repository\TestTransactionEventStreamStoreInterface;
use Webravo\Application\Event\EventStream;

class TestTransactionEventStreamRepository implements TestTransactionEventStreamRepositoryInterface
{
    // REPOSITORY USE STORE TO ACCESS DATA
    // REPOSITORY RECEIVE/RETURN ENTITY FROM/TO SERVICE
    // REPOSITORY RECEIVE/SEND DATA ARRAY FROM/TO STORE
    // REPOSITORY USE ENTITY toArray() and fromArray() TO CONVERT ENTITY <-> ARRAY

    protected $store;

    public function __construct(TestTransactionEventStreamStoreInterface $store = null)
    {
        if (is_null($store)) {
            $this->store = DependencyBuilder::resolve('tests\TestProject\Infrastructure\Repository\TestTransactionEventStreamStoreInterface');
        }
        else {
            $this->store = $store;
        }
    }

    public function getEventsByAggregateId($aggregate_id): ?EventStream
    {
        $a_stream = $this->store->getEventsByAggregateId($aggregate_id);
        $stream = EventStream::createByRawEvents($a_stream);
        return $stream;
    }

    public function persist(EventStream $stream): void {
        $this->store->persist($stream);
    }
}
