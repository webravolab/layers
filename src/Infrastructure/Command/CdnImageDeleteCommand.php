<?php

namespace Webravo\Infrastructure\Command;

use Webravo\Application\Command\CommandInterface;
use Webravo\Application\Command\GenericCommand;
use Webravo\Application\Exception\CommandException;

class CdnImageDeleteCommand extends GenericCommand implements CommandInterface {

    protected $command_name = 'CdnImageDeleteCommand';
    protected $url;

    public function __construct($url) {
        $this->url = $url;
    }

    public function getUrl() {
        return $this->url;
    }

    public function toArray(): array
    {
        $data = [
            'command' => $this->getCommandName(),
            'queue_name' => $this->getQueueName(),
            'binding_key' => $this->getBindingKey(),
            'header' => $this->getHeader(),
            'payload' => [
                'url' => $this->getUrl(),
            ]
        ];
        return $data;
    }

    public function fromArray(array $data)
    {
        if (isset($data['payload'])) {
            if (isset($data['payload']['url'])) {
                $this->url = $data['payload']['url'];
            }
        }
    }

    public static function buildFromArray(array $data): CommandInterface
    {
        if (isset($data['url']))
        {
            return self::construct($data['url']);
        }
        throw(new CommandException('Bad serialized command CdnImageDeleteCommand'));
    }
}