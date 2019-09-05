<?php

namespace tests\TestProject\Domain\AggregateRoot;

use tests\TestProject\Domain\Events\TestTransactionAddedEvent;
use tests\TestProject\Domain\Events\TestTransactionChangedStatusEvent;
use Webravo\Common\Domain\EventSourcedAggregateRoot;
use Webravo\Application\Event\EventStream;
use Webravo\Infrastructure\Library\DependencyBuilder;
use Webravo\Common\Domain\EventSourcedTrait;

class TestTransaction extends EventSourcedAggregateRoot
{
    use EventSourcedTrait;

    private $transaction_id;
    private $transaction_key;
    private $status;

    private $eventMap = [
        TestTransactionAddedEvent::class => 'testWhenTransactionAdded',
        TestTransactionChangedStatusEvent::class => 'testWhenTransactionStatusChanged',
    ];

    public function __construct($aggregate_id = null)
    {
        if ($aggregate_id) {
            $this->setAggregateId($aggregate_id);
            $this->setEventStream(new EventStream('TestTransaction', $aggregate_id));
        }
    }

    public function setAggregateId($aggregate_id)
    {
        $this->transaction_id = $aggregate_id;
    }

    public function getAggregateId()
    {
        return $this->transaction_id;
    }

    /**
     * Factory method to create an empty transaction
     */
    public static function newTransaction()
    {
        $guidService = DependencyBuilder::resolve('Webravo\Infrastructure\Service\GuidServiceInterface');
        $aggregate_id = $guidService->generate()->getValue();
        $e_transaction = new TestTransaction($aggregate_id);
        return $e_transaction;
    }

    public function createFrom($transactionKey): TestTransaction
    {
        $e_transaction = new TestTransaction();
        $e_transaction->setTransactionKey($transactionKey);
        return $e_transaction;
    }

    public function setTransactionKey($transaction_key)
    {
        $this->transaction_key = $transaction_key;
    }

    public function getTransactionKey()
    {
        return $this->transaction_key;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
    }

    // ---------------------------
    // Mutators
    // ---------------------------
    private function testWhenTransactionAdded(TestTransactionAddedEvent $event)
    {
        $this->setAggregateId($event->getAggregateId());
        $this->transaction_key = $event->getTransactionKey();
    }

    private function testWhenTransactionStatusChanged(TestTransactionChangedStatusEvent $event)
    {
        $this->setStatus($event->getStatus());
    }


    //
    // Serialization
    //

    public function toArray(): array
    {
         return [
             'aggregate_id' => $this->getAggregateId(),
             'transaction_key' => $this->getTransactionKey(),
             'status' => $this->getStatus(),
         ];
    }

    public function fromArray(array $a_values)
    {
        $this->setAggregateId($a_values['aggregate_id']);
        $this->setTransactionKey($a_values['transaction_key']);
        $this->setStatus($a_values['status']);
    }
}
