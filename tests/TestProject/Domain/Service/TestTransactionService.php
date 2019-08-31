<?php
namespace Project\Domain\Service;

use tests\TestProject\Domain\AggregateRoot\TestTransaction;
use tests\TestProject\Domain\Events\TestTransactionAddedEvent;
use tests\TestProject\Domain\Events\TestTransactionChangedStatusEvent;
use tests\TestProject\Domain\Repository\TestTransactionEventStreamRepositoryInterface;
use Webravo\Infrastructure\Repository\EventStreamRepositoryInterface;
use Webravo\Application\Command\CommandResponse;
use Webravo\Application\Service\CommandsQueueService;
use Webravo\Infrastructure\Library\DependencyBuilder;

class TestTransactionService
{
    private $repository;
    private $command_bus;

    // Here list only the command names ... handlers function are always named "when<command>()"
    private $command_map = [
        'TestTransactionCreateCommand',         // whenTestTransactionCreateCommand()
        'TestTransactionSetStatusCommand',      // whenTestTransactionSetStatusCommand()
    ];

    public function __construct(CommandsQueueService $command_bus, EventStreamRepositoryInterface $repository = null)
    {
        if (!$repository) {
            $repository = DependencyBuilder::resolve('tests\TestProject\Domain\Repository\TestTransactionEventStreamRepositoryInterface');
        }
        $this->repository = $repository;
        $this->command_bus = $command_bus;
        $this->command_bus->registerMapper($this->command_map, $this);
    }


    public function getTestTransactionById($aggregate_id)
    {
        $stream = $this->repository->getEventStreamByAggregateId('TestTransaction', $aggregate_id);
        // Rebuild the aggregate from the event stream
        $t = TestTransaction::rebuildFromHistory($stream);
        return $t;
    }


    /* ===================
       COMMAND HANDLERS
       =================== */

    public function whenTestTransactionCreateCommand($command): ?CommandResponse
    {
        $t = TestTransaction::newTransaction();
        $event = new TestTransactionAddedEvent($command->getTransactionKey(), $t->getAggregateId());
        $t->apply($event);
        $stream = $t->getChangedStream();
        // Aggregate just created ... persist the whole stream replacing any previous with the same aggregate_id
        $this->repository->persistStream($stream);
        return CommandResponse::withValue('ok', $stream);
    }

    public function whenTestTransactionSetStatusCommand($command): ?CommandResponse
    {
        $t = $this->getTestTransactionById($command->getTransactionId());
        $event = new TestTransactionChangedStatusEvent($command->getTransactionId(), $command->getStatus());
        // Apply the new event(s)
        $t->apply($event);
        // Save the new events to the Changed stream and persist to repository
        $stream = $t->getChangedStream();
        // Append change stream to existing stream for the aggregate_id
        $this->repository->addStreamToAggregateId($stream);
        // Return command status and Change stream for further dispatch
        return CommandResponse::withValue('ok', $stream);
    }

}
