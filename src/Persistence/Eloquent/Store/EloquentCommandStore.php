<?php

namespace Webravo\Persistence\Eloquent\Store;

use Webravo\Application\Command\CommandInterface;
use Webravo\Application\Command\GenericCommand;
use Webravo\Infrastructure\Repository\CommandStoreInterface;
use Webravo\Persistence\Eloquent\DataTable\JobDataTable;
use Webravo\Persistence\Eloquent\Hydrators\JobHydrator;

class EloquentCommandStore implements CommandStoreInterface {

    public function Append(CommandInterface $command)
    {
        $hydrator = new JobHydrator();
        $commandDataTable = new JobDataTable($hydrator);
        $commandDataTable->persistEntity($command);
    }

    public function getByGuid($guid): ?CommandInterface
    {
        $hydrator = new JobHydrator();
        $commandDataTable = new JobDataTable($hydrator);
        $a_command = $commandDataTable->getByGuid($guid);
        $command = GenericCommand::buildFromArray($a_command);
        return $command;
    }
}