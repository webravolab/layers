<?php

namespace test\TestProject\Domain\Commands;

use Webravo\Application\Command\CommandInterface;
use Webravo\Application\Command\GenericCommand;
use Webravo\Application\Exception\CommandException;

class TestCommand extends GenericCommand implements CommandInterface {

    protected $command_name = 'tests\Commands\TestCommand';
    protected $strParam1;
    protected $intParam2;
    protected $floatParam3;
    protected $clsParam4;
    protected $arrParam5;


    public function __construct($strParam1, $intParam2, $floatParam3, $clsParam4, $arrParam5) {
        parent::__construct();
        $this->strParam1 = $strParam1;
        $this->intParam2 = $intParam2;
        $this->floatParam3 = $floatParam3;
        $this->clsParam4 = $clsParam4;
        $this->arrParam5 = $arrParam5;
    }

    public function getParam1() {
        return $this->strParam1;
    }

    public function getParam2() {
        return $this->intParam2;
    }

    public function getParam3() {
        return $this->floatParam3;
    }

    public function getParam4() {
        return $this->clsParam4;
    }

    public function getParam5() {
        return $this->arrParam5;
    }

    public function toArray(): array
    {
        //  Get all base data from GenericEvent
        $data = parent::toArray();
        // Add all other data as "payload"
        $data += [
            'payload' => [
                'param1' => $this->getParam1(),
                'param2' => $this->getParam2(),
                'param3' => $this->getParam3(),
                'param4' => $this->getParam4(),
                'param5' => $this->getParam5(),
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
            if (isset($payload_data['param2'])) {
                $this->intParam2 = $payload_data['param2'];
            }
            if (isset($payload_data['param3'])) {
                $this->floatParam3 = $payload_data['param3'];
            }
            if (isset($payload_data['param4'])) {
                $this->clsParam4 = $payload_data['param4'];
            }
            if (isset($payload_data['param5'])) {
                $this->arrParam5 = $payload_data['param5'];
            }
        }
    }

    public static function buildFromArray(array $data): CommandInterface
    {
        return new static($data['param1'], $data['param2'], $data['param3'], $data['param4'], $data['param5']);
    }
}