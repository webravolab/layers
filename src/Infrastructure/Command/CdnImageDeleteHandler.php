<?php

namespace Webravo\Infrastructure\Command;

use Webravo\Application\Command\CommandHandlerInterface;
use Webravo\Application\Command\CommandInterface;
use Webravo\Application\Exception\CommandException;
use Webravo\Application\Command\CommandResponse;
use Webravo\Infrastructure\Service\CdnServiceInterface;
use Psr\Log\LoggerInterface;

class CdnImageDeleteHandler implements CommandHandlerInterface {

    protected $cdnService;

    public function __construct(CdnServiceInterface $cdnService, LoggerInterface $loggerService) {
        $this->cdnService = $cdnService;
        $this->loggerService = $loggerService;
    }

    public function Handle(CommandInterface $command)
    {
        if (!$command instanceof CdnImageDeleteCommand) {
            throw new CommandException('CdnImageDeleteHandler can only handle CdnImageDeleteCommand');
        }
        try {
            $url = $command->getUrl();
            $result = $this->cdnService->deleteImageFromCdn($url);
        }
        catch(\Exception $e) {
            $this->loggerService->warning('[CdnImageDeleteHandler] error deleting url from cdn: ' . $url . ' - ' . $e->getMessage());
            $result = false;
        }
        return CommandResponse::withValue($result);
    }

    public function listenTo() {
        return CdnImageDeleteCommand::class;
    }
}
