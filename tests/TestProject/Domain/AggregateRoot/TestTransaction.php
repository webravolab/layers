<?php

namespace tests\TestProject\Domain\AggregateRoot;

use tests\TestProject\Domain\Events\TestTransactionAddedEvent;
use Webravo\Common\Domain\EventSourcedAggregateRoot;
use Webravo\Application\Event\EventStream;
use Webravo\Infrastructure\Library\DependencyBuilder;
use Webravo\Common\Domain\EventSourcedTrait;

class TestTransaction extends EventSourcedAggregateRoot
{

    use EventSourcedTrait;

    private $transaction_id;
    private $transaction_key;

    private $eventMap = [
        TestTransactionAddedEvent::class => 'testApplyTransactionAdded',
    ];

    public function __construct($aggregate_id)
    {
        $this->transaction_id = $aggregate_id;
    }

    public function createFrom($transactionKey): TestTransaction
    {
        $e_transaction = new TestTransaction();
        $e_transaction->setTransactionKey($transactionKey);
        return $e_transaction;
    }

    public static function rebuild(EventStream $eventStream)
    {
        $transaction = new static($eventStream->getAggregateId());
    }

    /**
     * Factory method to create a transaction
     */
    public static function newTransaction()
    {
        $guidService = DependencyBuilder::resolve('Webravo\Infrastructure\Service\GuidServiceInterface');
        $aggregate_id = $guidService->generate()->getValue();
        $e_transaction = new TestTransaction($aggregate_id);
        return $e_transaction;
    }

    /**
     * Return aggregate root ID
     * @return string
     */
    public function getAggregateRootId(): string
    {
        return $this->transaction_id;
    }

    public function setTransactionKey($transaction_key)
    {
        $this->transaction_key = $transaction_key;
    }

    public function getTransactionKey()
    {
        return $this->transaction_key;
    }

    // ---------------------------
    // Events Applier
    // ---------------------------
    private function testApplyTransactionAdded(TestTransactionAddedEvent $event)
    {
        $this->transaction_key = $event->getTransactionKey();
    }
}
