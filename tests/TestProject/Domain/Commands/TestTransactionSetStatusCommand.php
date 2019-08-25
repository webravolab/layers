<?php

namespace tests\TestProject\Domain\Commands;

use Webravo\Application\Command\GenericCommand;
use Webravo\Application\Exception\CommandException;
use Webravo\Application\Command\CommandInterface;

class TestTransactionSetStatusCommand extends GenericCommand
{
    protected $command_name = 'tests\Commands\TestTransactionSetStatusCommand';
    protected $transaction_id;
    protected $status;

    public function __construct($transaction_id, $status) {
        parent::__construct();
        $this->transaction_id = $transaction_id;
        $this->status = $status;
    }

    public function getTransactionId()
    {
        return $this->transaction_id;
    }

    public function getStatus() {
        return $this->status;
    }

    public function toArray(): array
    {
        //  Get all base data from GenericEvent
        $data = parent::toArray();
        // Add all other data as "payload"
        $data += [
            'payload' => [
                'transaction_id' => $this->getTransactionId(),
                'status' => $this->getStatus(),
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
            if (isset($payload_data['transaction_id'])) {
                $this->transaction_id = $payload_data['transaction_id'];
            }
            if (isset($payload_data['status'])) {
                $this->status = $payload_data['status'];
            }
        }
    }

    public static function buildFromArray(array $data): CommandInterface
    {
        return new static($data['transaction_id'], $data['status']);
    }
}