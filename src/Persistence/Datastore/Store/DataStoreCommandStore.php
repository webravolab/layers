<?php

namespace Webravo\Persistence\Datastore\Store;

use Webravo\Common\Entity\EventEntity;
use Webravo\Infrastructure\Library\DependencyBuilder;
use Webravo\Application\Command\GenericCommand;
use Webravo\Infrastructure\Repository\CommandStoreInterface;
use Webravo\Common\Entity\CommandEntity;
use Webravo\Application\Command\CommandInterface;
use Webravo\Persistence\Datastore\DataTable\CommandDataStoreTable;
use Webravo\Persistence\Hydrators\CommandHydrator;

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
        $serialized_command = $domainCommand->getSerializedCommand();
        $e_command = CommandEntity::buildFromArray($a_values);
        $e_command->setPayload($serialized_command);

        $entity_name = get_class($e_command);
        $hydrator = new CommandHydrator();
        $commandDataTable = new CommandDataStoreTable($this->dataStoreService, $hydrator);
        $commandDataTable->persistEntity($e_command);
   }

    public function getByGuid($guid): ?CommandInterface
    {
        $hydrator = new CommandHydrator();
        $commandDataTable = new CommandDataStoreTable($this->dataStoreService, $hydrator);
        $a_command = $commandDataTable->getByGuid($guid);
        $command = GenericCommand::buildFromArray($a_command->toArray());
        return $command;
    }
}