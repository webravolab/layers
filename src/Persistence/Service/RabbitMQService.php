<?php

namespace Webravo\Persistence\Service;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Webravo\Infrastructure\Service\QueueServiceInterface;
use Webravo\Infrastructure\Library\Configuration;
use \Exception;

class RabbitMQService implements QueueServiceInterface
{

    private $connection;
    private $channel;
    private $defaultQueue = 'trash-queue';  // Default: go to trash
    private $defaultStrategy = '';          // Default: named queue
    private $exchangeName = '';             // Default: unnamed strategy
    private $bindingKey = '';               // Default: no routing binding key

    public function __construct()
    {
        $this->defaultQueue = Configuration::get('DEFAULT_QUEUE', null, 'trash-queue');
        $this->defaultStrategy = '';
        $this->exchangeName = '';
        $this->bindingKey = '';

        $host = Configuration::get('host', 'rabbitmq', 'localhost');
        $port = Configuration::get('port', 'rabbitmq', '5672');
        $user = Configuration::get('user', 'rabbitmq', 'guest');
        $password = Configuration::get('password', 'rabbitmq', 'guest');
        $virtual_host = Configuration::get('virtual_host', 'rabbitmq', '/');

        $this->connection = new AMQPStreamConnection($host, $port, $user, $password, $virtual_host);

        $this->channel = $this->connection->channel();
        $this->channel->basic_qos(null, 1, null);
    }

    public function setDefaultQueue(string $queueName)
    {
        $this->defaultQueue = $queueName;
    }

    public function getDefaultQueue()
    {
        return $this->defaultQueue;
    }

    public function createChannel(string $strategy, string $exchange_name, string $bindingKey = null)
    {
        switch($strategy) {
            case '';
            case 'direct':
            case 'topic':
            case 'fanout':
                $this->channel->exchange_declare($exchange_name, $strategy, false, true, false, false);
                $this->exchangeName = $exchange_name;
                $this->defaultStrategy = $strategy;
                $this->bindingKey = $bindingKey ?? '';
                break;
            default:
                throw new \Exception('Bad queue strategy: ' . $strategy);
                break;
        }
    }

    public function getDefaultStrategy()
    {
        return $this->defaultStrategy;
    }

    public function getDefaultChannelName()
    {
        return $this->exchangeName;
    }

    public function createQueue(string $queueName): int
    {
        $a_queue_status = $this->channel->queue_declare($queueName, false, true, false, false);
        if (is_array($a_queue_status) && isset($a_queue_status[1])) {
            return $a_queue_status[1];
        }
        return 0;
    }

    public function subscribeQueue(string $queueName, string $exchangeName = null, string $bindingKey = null)
    {
        if (!empty($exchangeName)) {
            $this->exchangeName = $exchangeName;
        }
        if (!empty($bindingKey)) {
            $this->bindingKey = $bindingKey;
        }
        $this->channel->queue_bind($queueName, $this->exchangeName, $this->bindingKey);
    }

    public function unsubscribeQueue(string $queueName, string $exchangeName = null, string $bindingKey = null)
    {
        if (!empty($exchangeName)) {
            $this->exchangeName = $exchangeName;
        }
        if (!empty($bindingKey)) {
            $this->bindingKey = $bindingKey;
        }
        $this->channel->queue_unbind($queueName, $this->exchangeName, $this->bindingKey);
    }
    
    public function publishMessage($message, $queueName = null, $bindingKey = null, array $header = []): ?string
    {
        $bindingKey = $bindingKey ?? '';
        $msg = new AMQPMessage($message, $header);
        if (empty($queueName) && !empty($this->defaultQueue)) {
            $queueName = $this->defaultQueue;
        }
        $this->createQueue($queueName);
        $this->channel->basic_publish($msg, empty($this->exchangeName) ? '' : $this->exchangeName, empty($this->exchangeName) ? $queueName : $bindingKey);
        // $this->channel->basic_publish($msg,  '', $queueName);

        return null;    // TODO
    }

    public function waitMessages($queueName, $callback)
    {
        if (empty($queueName) && !empty($this->defaultQueue)) {
            $queueName = $this->defaultQueue;
        }
        $this->createQueue($queueName);
        $this->channel->queue_bind($queueName, $this->exchangeName, $this->bindingKey);
        $this->channel->basic_consume($queueName, '', false, false, false, false, $callback);
        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }

    public function processSingleMessage($queueName, $callback)
    {
        if (empty($queueName) && !empty($this->defaultQueue)) {
            $queueName = $this->defaultQueue;
        }
        $this->createQueue($queueName);
        if (!empty($this->exchangeName)) {
            $this->channel->queue_bind($queueName, $this->exchangeName, $this->bindingKey);
        }
        $this->channel->basic_consume($queueName, '', false, false, false,  false, $callback);
        if (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }

    public function getSingleMessage($queueName = null) {
        if (empty($queueName) && !empty($this->defaultQueue)) {
            $queueName = $this->defaultQueue;
        }
        return $this->channel->basic_get($queueName, false, null);
    }

    public function messageAcknowledge($message)
    {
        $this->channel->basic_ack($message->delivery_info['delivery_tag']);
    }

    public function messageNotAcknowledge($message)
    {
        $this->channel->basic_nack($message->delivery_info['delivery_tag']);
    }

    public function close()
    {
        $this->channel->close();
        $this->connection->close();
    }

    public function delete()
    {
        // Alias of close()
        $this->close();
    }

}