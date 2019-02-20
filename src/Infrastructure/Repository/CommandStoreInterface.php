<?php

namespace Webravo\Infrastructure\Repository;

use Webravo\Application\Command\CommandInterface;

interface CommandStoreInterface {

    public function Append(CommandInterface $command);

}