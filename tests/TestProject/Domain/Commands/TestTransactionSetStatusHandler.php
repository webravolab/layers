<?php

namespace tests\TestProject\Domain\Commands;

use tests\TestProject\Domain\AggregateRoot\TestTransaction;
use tests\TestProject\Domain\Repository\TestTransactionEventStreamRepositoryInterface;
use Webravo\Application\Command\CommandHandlerInterface;
use Webravo\Application\Command\CommandInterface;
use Webravo\Application\Exception\CommandException;
use Webravo\Application\Command\CommandResponse;
use \tests\TestProject\Domain\Commands\TestTransactionSetStatusCommand;
use tests\TestProject\Domain\Events\TestTransactionChangedStatusEvent;
use Webravo\Infrastructure\Library\DependencyBuilder;


class TestTransactionSetStatusHandler implements CommandHandlerInterface
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
        if (!$command instanceof TestTransactionSetStatusCommand) {
            throw new CommandException('TestTransactionSetStatusHandler can only handle TestTransactionSetStatusCommand');
        }
        $event = new TestTransactionChangedStatusEvent($command->getTransactionId(), $command->getStatus());
        // TODO read transaction from reposiroty by its aggregate id

        $stream = $this->repository->getEventsByAggregateId($command->getTransactionId());
        $t = TestTransaction::rebuildFromHistory($stream);
        $t->recordAndApplyThat($event);
        $stream = $t->getEventStream();
        $this->repository->persist($stream);
        return CommandResponse::withValue('ok', $stream);
    }

    public function listenTo()
    {
        return TestTransactionCreateCommand::class;
    }
}
