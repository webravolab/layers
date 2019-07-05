<?php

namespace Webravo\Infrastructure\Repository;

use Webravo\Application\Command\CommandInterface;

/**
 * The "Command Store" is a simply command bucket sink to log all commands
 * It is NOT used for command dispatching or process
 *
 * Interface CommandStoreInterface
 * @package Webravo\Infrastructure\Repository
 */
interface CommandStoreInterface {

    public function Append(CommandInterface $command);

    // TODO
    // Implements other methods to retrieve or filter commands

}