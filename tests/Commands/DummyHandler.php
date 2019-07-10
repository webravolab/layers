<?php

namespace tests\Commands;

use Webravo\Application\Command\CommandHandlerInterface;
use Webravo\Application\Command\CommandInterface;
use Webravo\Application\Command\CommandResponse;

class DummyHandler implements CommandHandlerInterface
{

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
