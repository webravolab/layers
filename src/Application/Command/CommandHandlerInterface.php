<?php

namespace Webravo\Application\Command;

interface CommandHandlerInterface {
    public function Handle(CommandInterface $command);
}