<?php

namespace Webravo\Persistence\Eloquent\Store;

use Webravo\Application\Command\CommandInterface;
use Webravo\Application\Command\GenericCommand;
use Webravo\Infrastructure\Repository\CommandRepositoryInterface;
use Webravo\Persistence\Eloquent\DataTable\CommandDataTable;
use Webravo\Persistence\Hydrators\CommandHydrator;
use Webravo\Common\Entity\CommandEntity;

class EloquentCommandStore implements CommandRepositoryInterface {

    public function append(CommandInterface $command)
    {
        $a_values = $command->toArray();
        $serialized_command = $command->getSerializedCommand();
        $e_command = CommandEntity::buildFromArray($a_values);
        $e_command->setPayload($serialized_command);
        $hydrator = new CommandHydrator();
        $commandDataTable = new CommandDataTable($hydrator);
        $commandDataTable->persistEntity($e_command);
    }

    public function getByGuid(string $guid): ?CommandInterface
    {
        $hydrator = new CommandHydrator();
        $commandDataTable = new CommandDataTable($hydrator);
        $a_command = $commandDataTable->getByGuid($guid);
        $a_encapsulated_command = $a_command['payload'];
        $command = GenericCommand::buildFromArray($a_encapsulated_command);
        return $command;
    }
}