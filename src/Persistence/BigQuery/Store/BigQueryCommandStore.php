<?php

namespace Webravo\Persistence\BigQuery\Store;

use Webravo\Common\Entity\EventEntity;
use Webravo\Infrastructure\Library\DependencyBuilder;
use Webravo\Application\Command\GenericCommand;
use Webravo\Infrastructure\Repository\CommandRepositoryInterface;
use Webravo\Common\Entity\CommandEntity;
use Webravo\Application\Command\CommandInterface;
use Webravo\Persistence\BigQuery\DataTable\CommandBigQueryTable;
use Webravo\Persistence\Hydrators\CommandHydrator;

/**
 * The "Command Store" is a simply command bucket sink to log all commands
 * It is NOT used for command dispatching or process
 */

class BigQueryCommandStore implements CommandRepositoryInterface {

    private $bigQueryService;

    public function __construct()
    {
        $this->bigQueryService = DependencyBuilder::resolve('Webravo\Infrastructure\Service\BigQueryServiceInterface');
    }

    public function append(CommandInterface $domainCommand)
    {
        $a_values = $domainCommand->toArray();
        $serialized_command = $domainCommand->getSerializedCommand();
        $e_command = CommandEntity::buildFromArray($a_values);
        $e_command->setPayload($serialized_command);
        $hydrator = new CommandHydrator();
        $commandDataTable = new CommandBigQueryTable($this->bigQueryService, $hydrator);
        $commandDataTable->persistEntity($e_command);
   }

    public function getByGuid(string $guid): ?CommandInterface
    {
        $hydrator = new CommandHydrator();
        $commandDataTable = new CommandBigQueryTable($this->bigQueryService, $hydrator);
        $a_command = $commandDataTable->getByGuid($guid);
        $a_encapsulated_command = $a_command['payload'];
        $command = GenericCommand::buildFromArray($a_encapsulated_command);
        return $command;
    }
}