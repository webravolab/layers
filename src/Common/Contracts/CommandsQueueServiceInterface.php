<?php
namespace Webravo\Common\Contracts;

use Webravo\Application\Command\CommandInterface;

interface CommandsQueueServiceInterface {

    public function dispatchCommand(CommandInterface $command);

    public function processCommandQueue();

}
