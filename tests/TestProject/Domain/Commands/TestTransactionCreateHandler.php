<?php

namespace tests\TestProject\Domain\Commands;

use tests\TestProject\Domain\AggregateRoot\TestTransaction;
use tests\TestProject\Domain\Events\TestTransactionAddedEvent;
use tests\TestProject\Domain\Repository\TestTransactionEventStreamRepositoryInterface;
use Webravo\Application\Command\CommandHandlerInterface;
use Webravo\Application\Command\CommandInterface;
use Webravo\Application\Exception\CommandException;
use Webravo\Application\Command\CommandResponse;
use tests\TestProject\Domain\Commands\TestTransactionCreateCommand;
use Webravo\Infrastructure\Library\DependencyBuilder;

class TestTransactionCreateHandler implements CommandHandlerInterface
{
    private $repository;

    public function __construct(TestTransactionEventStreamRepositoryInterface $repository = null)
    {
        if (!$repository) {
            $repository = DependencyBuilder::resolve('tests\TestProject\Domain\Repository\TestTransactionEventStreamRepositoryInterface');
        }
        $this->repository = $repository;
    }

    public function handle(CommandInterface $command)
    {
        if (!$command instanceof TestTransactionCreateCommand) {
            throw new CommandException('TestTransactionCreateHandler can only handle TestTransactionCreateCommand');
        }
        $t = TestTransaction::newTransaction();
        $event = new TestTransactionAddedEvent($command->getTransactionKey(), $t->getAggregateId());
        $t->recordAndApplyThat($event);
        $stream = $t->getEventStream();
        $this->repository->persist($stream);
        return CommandResponse::withValue('ok', $stream);
    }

    /*
    public function listenTo()
    {
        return TestTransactionCreateCommand::class;
    }
    */
}
