<?php

namespace tests\TestProject\Domain\Projection;

use Google\Cloud\Datastore\Transaction;
use tests\TestProject\Domain\AggregateRoot\TestTransaction;
use Webravo\Application\Event\AggregateDomainEvent;
use Webravo\Application\Event\EventInterface;
use Webravo\Application\Event\GenericEvent;
use Webravo\Common\Contracts\EventsQueueServiceInterface;
use Webravo\Common\Contracts\RepositoryInterface;
use tests\TestProject\Domain\Events\TestTransactionAddedEvent;
use tests\TestProject\Domain\Events\TestTransactionChangedStatusEvent;


class TestTransactionByStatusProjection
{
    public $testValue = 0;      // To test instances

    private $repository;

    private $event_subscribed = [
        TestTransactionAddedEvent::class,
        TestTransactionChangedStatusEvent::class,
    ];

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function subscribe(EventsQueueServiceInterface $service)
    {
        $service->registerMapper($this->event_subscribed, $this);
    }

    public function handle(AggregateDomainEvent $event): void
    {
        $type = $event->getType();
        $here = $this->testValue;
        switch ($type)
        {
            case 'TestTransactionAddedEvent':
                $transaction = TestTransaction::newTransaction();
                $transaction->setAggregateId($event->getAggregateId());
                $transaction->setTransactionKey($event->getTransactionKey());
                $this->repository->persist($transaction);
                break;
            case 'TestTransactionChangedStatusEvent':
                $aggregate_id = $event->getAggregateId();
                $transaction = $this->repository->getByGuid($aggregate_id);
                if (!$transaction) {
                    // EXCEPTION
                }
                $transaction->setStatus($event->getStatus());
                $this->repository->update($transaction);
                break;
        }
    }

    private function testApplyTransactionAdded(TestTransactionAddedEvent $event)
    {
        $aggregate_id = $event->getAggregateId();
        $transaction_key = $event->getTransactionKey();
        // TODO
    }

    private function testApplyTransactionStatusChanged(TestTransactionChangedStatusEvent $event)
    {
        $status = $event->getStatus();
        // TODO
    }

}
