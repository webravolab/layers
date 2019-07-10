<?php

namespace tests\Commands;

use Webravo\Application\Command\CommandInterface;
use Webravo\Application\Command\GenericCommand;

class TestWithoutHandlerCommand extends GenericCommand implements CommandInterface {

    protected $command_name = 'tests\Commands\TestWithoutHandlerCommand';
    protected $strParam1;


    public function __construct($strParam1) {
        parent::__construct();
        $this->strParam1 = $strParam1;
    }

    public function getParam1() {
        return $this->strParam1;
    }

    public function toArray(): array
    {
        //  Get all base data from GenericEvent
        $data = parent::toArray();
        // Add all other data as "payload"
        $data += [
            'payload' => [
                'param1' => $this->getParam1(),
            ]
        ];
        return $data;
    }

    public function fromArray(array $data)
    {
        if (isset($data['payload'])) {
            $payload_data = $data['payload'];
            if (isset($payload_data['param1'])) {
                $this->strParam1 = $payload_data['param1'];
            }
        }
    }

    public static function buildFromArray(array $data): CommandInterface
    {
        return new static($data['param1']);
    }
}