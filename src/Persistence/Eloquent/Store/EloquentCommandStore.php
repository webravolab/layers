<?php

namespace Webravo\Persistence\Eloquent\Store;

use Webravo\Application\Command\CommandInterface;
use Webravo\Application\Command\GenericCommand;
use Webravo\Infrastructure\Repository\CommandRepositoryInterface;
use Webravo\Persistence\Eloquent\DataTable\CommandDataTable;
use Webravo\Persistence\Hydrators\CommandHydrator;

class EloquentCommandStore implements CommandRepositoryInterface {

    public function Append(CommandInterface $command)
    {
        $hydrator = new CommandHydrator();
        $commandDataTable = new CommandDataTable($hydrator);
        $commandDataTable->persistEntity($command);
    }

    public function getByGuid($guid): ?CommandInterface
    {
        $hydrator = new CommandHydrator();
        $commandDataTable = new CommandDataTable($hydrator);
        $a_command = $commandDataTable->getByGuid($guid);
        $command = GenericCommand::buildFromArray($a_command);
        return $command;
    }
}