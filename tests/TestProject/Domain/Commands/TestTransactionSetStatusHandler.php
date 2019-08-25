<?php

namespace tests\TestProject\Domain\Commands;

use tests\TestProject\Domain\AggregateRoot\TestTransaction;
use Webravo\Application\Command\CommandHandlerInterface;
use Webravo\Application\Command\CommandInterface;
use Webravo\Application\Exception\CommandException;
use Webravo\Application\Command\CommandResponse;
use \tests\TestProject\Domain\Commands\TestTransactionSetStatusCommand;
use tests\TestProject\Domain\Events\TestTransactionChangedStatusEvent;


class TestTransactionSetStatusHandler implements CommandHandlerInterface
{

    public function handle(CommandInterface $command)
    {
        if (!$command instanceof TestTransactionSetStatusCommand) {
            throw new CommandException('TestTransactionSetStatusHandler can only handle TestTransactionSetStatusCommand');
        }
        $event = new TestTransactionChangedStatusEvent($command->getTransactionId(), $command->getStatus());
        // TODO read transaction from reposiroty by its aggregate id
        $t = TestTransaction::newTransaction();
        $t->recordAndApplyThat($event);
        $stream = $t->getEventStream();
        return CommandResponse::withValue('ok', $stream);
    }

    public function listenTo()
    {
        return TestTransactionCreateCommand::class;
    }
}
