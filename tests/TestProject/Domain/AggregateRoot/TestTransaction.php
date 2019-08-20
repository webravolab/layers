<?php

namespace tests\TestProject\Domain\AggregateRoot;

use Webravo\Common\Domain\EventSourcedAggregateRoot;

class TestTransaction extends EventSourcedAggregateRoot
{
    private $transaction_id;

    /**
     * Factory method to create a transaction
     */
    public static function newTransaction()
    {
        $transaction = new self();

        return $transaction;

    }

    /**
     * Return aggregate root ID
     * @return string
     */
    public function getAggregateRootId(): string
    {
        return $this->transaction_id;
    }

}
