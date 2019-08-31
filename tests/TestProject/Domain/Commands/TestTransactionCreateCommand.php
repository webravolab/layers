<?php

namespace tests\TestProject\Domain\Commands;

use Webravo\Application\Command\GenericCommand;
use Webravo\Application\Exception\CommandException;
use Webravo\Application\Command\CommandInterface;

class TestTransactionCreateCommand extends GenericCommand
{
    protected $command_name = 'tests\Commands\TestTransactionCreateCommand';
    protected $transaction_key;

    public function __construct($transaction_key) {
        parent::__construct();
        $this->transaction_key = $transaction_key;
    }

    public function getTransactionKey() {
        return $this->transaction_key;
    }

    public function toArray(): array
    {
        //  Get all base data from GenericEvent
        $data = parent::toArray();
        // Add all other data as "payload"
        $data += [
            'payload' => [
                'transaction_key' => $this->getTransactionKey(),
            ]
        ];
        return $data;
    }

    public function fromArray(array $data)
    {
        // Get base properties
        parent::fromArray($data);

        // Get custom properties
        if (isset($data['payload'])) {
            $payload_data = $data['payload'];
            if (isset($payload_data['transaction_key'])) {
                $this->transaction_key = $payload_data['transaction_key'];
            }
        }
    }

    public static function buildFromArray(array $data): CommandInterface
    {
        return new static($data['transaction_key']);
    }
}