<?php

namespace tests\TestProject\Domain\Commands;

use Webravo\Application\Command\CommandHandlerInterface;
use Webravo\Application\Command\CommandInterface;
use Webravo\Application\Exception\CommandException;
use Webravo\Application\Command\CommandResponse;

class TestHandler implements CommandHandlerInterface
{

    public function handle(CommandInterface $command)
    {
        if (!$command instanceof TestCommand) {
            throw new CommandException('TestCommandHandler can only handle TestCommand');
        }
        return CommandResponse::withValue('ok');
    }

    public function listenTo()
    {
        return TestCommand::class;
    }
}
