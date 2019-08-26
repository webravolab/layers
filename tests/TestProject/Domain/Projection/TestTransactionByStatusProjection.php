<?php

namespace tests\TestProject\Domain\Projection;

use Webravo\Common\Contracts\EventsQueueServiceInterface;
use Webravo\Common\Contracts\RepositoryInterface;
use tests\TestProject\Domain\Events\TestTransactionAddedEvent;
use tests\TestProject\Domain\Events\TestTransactionChangedStatusEvent;


class TestTransactionByStatusProjection
{

    private $repository;

    private $eventMap = [
        TestTransactionAddedEvent::class => 'testApplyTransactionAdded',
        TestTransactionChangedStatusEvent::class => 'testApplyTransactionStatusChanged',
    ];

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }


    public function subscribe(EventsQueueServiceInterface $service)
    {
        $service->registerMapper($this->eventMap);
        /*
        foreach($this->eventMap as $class => $handler) {
            $service->registerHandler($handler);
        }
        */
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
