<?php

namespace tests\TestProject\Domain\Commands;

use Psr\Log\LoggerInterface;
use Webravo\Application\Command\CommandHandlerInterface;
use Webravo\Application\Command\CommandInterface;
use Webravo\Application\Command\CommandResponse;

class DummyHandler implements CommandHandlerInterface
{
    private $logger;


    // Logger injected in constructor to test Command Handler Builder with dependencies!
    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    public function Handle(CommandInterface $command)
    {
        return CommandResponse::withValue('dummy says ok');
    }

    /*
    public function listenTo()
    {
        return TestCommand::class;
    }
    */
}
