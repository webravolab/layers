<?php

namespace Webravo\Application\Command;

interface CommandBusInterface {

    public function Execute(CommandInterface $command);

}