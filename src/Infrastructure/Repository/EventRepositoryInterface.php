<?php

namespace Webravo\Infrastructure\Repository;

use Webravo\Application\Event\EventInterface;

/**
 * The "Event Store" is a simply event bucket sink to log all events
 * It is NOT used for event dispatching or process
 *
 * Interface EventRepositoryInterface
 * @package Webravo\Infrastructure\Repository
 */
interface EventRepositoryInterface {

    public function Append(EventInterface $domainEvent);

    // public function AllEvents();

    // TODO
    // Implements other methods to retrieve or filter commands

}