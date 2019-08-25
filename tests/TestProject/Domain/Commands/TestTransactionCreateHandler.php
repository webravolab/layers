<?php

namespace tests\TestProject\Domain\Commands;

use tests\TestProject\Domain\AggregateRoot\TestTransaction;
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
        $e_transaction = TestTransaction::newTransaction();
        $e_transaction->createFrom($command->getTransactionKey());

        return CommandResponse::withValue('ok');
    }

    public function listenTo()
    {
        return TestTransactionCreateCommand::class;
    }
}
