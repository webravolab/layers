<?php

namespace Webravo\Persistence\Datastore\Store;

use Webravo\Infrastructure\Library\DependencyBuilder;
use Webravo\Application\Command\GenericCommand;
use Webravo\Infrastructure\Repository\CommandStoreInterface;
use Webravo\Common\Entity\DataStoreCommandEntity;
use Webravo\Application\Command\CommandInterface;
use Webravo\Persistence\Datastore\DataTable\CommandDataStoreTable;
/**
 * The "Command Store" is a simply command bucket sink to log all commands
 * It is NOT used for command dispatching or process
 */

class DataStoreCommandStore implements CommandStoreInterface {

    private $dataStoreService;

    public function __construct()
    {
        $this->dataStoreService = DependencyBuilder::resolve('Webravo\Infrastructure\Service\DataStoreServiceInterface');
    }

    public function Append(CommandInterface $domainCommand)
    {
        $a_values = $domainCommand->toArray();
        $e_command = DataStoreCommandEntity::buildFromArray($a_values);
        $entity_name = get_class($e_command);
        $commandDataTable = new CommandDataStoreTable($this->dataStoreService);
        $commandDataTable->persistEntity($e_command);
   }

    public function getByGuid($guid): ?CommandInterface
    {
        $commandDataTable = new CommandDataStoreTable($this->dataStoreService);
        $a_command = $commandDataTable->getByGuid($guid);
        $command = GenericCommand::buildFromArray($a_command->toArray());
        return $command;
    }
}