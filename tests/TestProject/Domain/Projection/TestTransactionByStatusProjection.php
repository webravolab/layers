<?php

namespace tests\TestProject\Domain\Projection;

use Webravo\Common\Contracts\RepositoryInterface;
use tests\TestProject\Domain\Events\TestTransactionAddedEvent;
use tests\TestProject\Domain\Events\TestTransactionChangedStatusEvent;

class TestTransactionByStatusProjection
{

    private $repository;

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }


    public function subscribe(EventQueueService $service)
    {

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
