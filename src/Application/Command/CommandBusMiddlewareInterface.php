<?php

namespace Webravo\Application\Command;

use Webravo\Application\Command\CommandResponse;

interface CommandBusMiddlewareInterface {

    public function dispatch(CommandInterface $command): ?CommandResponse;

    public function subscribeHandlerMapper(array $mapper, $class_name): void;

}
