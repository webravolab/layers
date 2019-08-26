<?php

namespace tests\TestProject\Domain\Commands;

use tests\TestProject\Domain\AggregateRoot\TestTransaction;
use tests\TestProject\Domain\Events\TestTransactionAddedEvent;
use Webravo\Application\Command\CommandHandlerInterface;
use Webravo\Application\Command\CommandInterface;
use Webravo\Application\Exception\CommandException;
use Webravo\Application\Command\CommandResponse;
use tests\TestProject\Domain\Commands\TestTransactionCreateCommand;

class TestTransactionCreateHandler implements CommandHandlerInterface
{

    public function handle(CommandInterface $command)
    {
        if (!$command instanceof TestTransactionCreateCommand) {
            throw new CommandException('TestTransactionCreateHandler can only handle TestTransactionCreateCommand');
        }

        $t = TestTransaction::newTransaction();
        $event = new TestTransactionAddedEvent($command->getTransactionKey(), $t->getAggregateId());
        $t->recordAndApplyThat($event);
        $stream = $t->getEventStream();
        return CommandResponse::withValue('ok', $stream);
    }

    public function listenTo()
    {
        return TestTransactionCreateCommand::class;
    }
}