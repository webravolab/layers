<?php

namespace Webravo\Infrastructure\Repository;

use Webravo\Common\Contracts\DomainEventInterface;

interface EventStoreInterface {
    public function Append(DomainEventInterface $domainEvent);

    public function AllEvents();



}