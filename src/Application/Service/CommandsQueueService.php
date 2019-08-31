<?php
namespace Webravo\Application\Service;

use Webravo\Common\Contracts\CommandsQueueServiceInterface;
use Webravo\Application\Command\CommandBucketBusMiddleware;
use Webravo\Application\Command\CommandBusFactory;
use Webravo\Application\Command\CommandInterface;
use Webravo\Application\Command\CommandLoggerBusMiddleware;
use Webravo\Application\Command\CommandRemoteBusMiddleware;
use Webravo\Application\Command\CommandBusDispatcher;
use Webravo\Application\Command\GenericCommand;
use Webravo\Persistence\Service\DBQueueService;
use Webravo\Persistence\Service\NullLoggerService;
use Webravo\Persistence\Service\NullQueueService;
use Webravo\Persistence\Service\RabbitMQService;
use Webravo\Persistence\Service\StackDriverLoggerService;
use Webravo\Persistence\Eloquent\Store\EloquentJobStore;
use Webravo\Persistence\Eloquent\Store\EloquentCommandStore;
use Webravo\Persistence\Datastore\Store\DataStoreCommandStore;
use Webravo\Persistence\BigQuery\Store\BigQueryCommandStore;
use Webravo\Infrastructure\Library\Configuration;
use Webravo\Infrastructure\Library\DependencyBuilder;

class CommandsQueueService implements CommandsQueueServiceInterface
{
    /**
     * Singleton instance
     * @var self instance
     */
    private static $instance = null;

    /**
     * Instance of QueueServiceInterface to access the remote event bus queue
     * @var
     */
    protected $commandQueueService;

    /**
     * Array of Instances of QueueServiceInterface to access the remote event bus queues
     * @array
     */
    // protected $a_eventQueueServices = [];

    /**
     * The Command bus used to dispatch commands
     * @var instance of CommandBusInterface
     */
    protected $commandBus;

    /**
     * The command store (bucket)
     * @var instance of CommandStoreInterface
     */
    protected $commandStoreRepository;

    /**
     * The command bus used to store commands in store bucket
     * @var instance of CommandBucketBusMiddleware
     */
    protected $commandBusStore;

    /**
     * The local command dispatcher used to dispatch locally commands received by remote bus
     * @var instance of CommandBusInterface
     */
    protected $localCommandDispatcher;

    /**
     * The remote queue name
     * @var
     */
    protected $queueName;

    /**
     * Instance of Logger to use
     * @var
     */
    protected $loggerService;

    /**
     * The ecurrent environment as defined in ENV
     * @var string
     */
    private $_environment;

    public function __construct($options = [])
    {
        $this->_environment = Configuration::get('APP_ENV');

        // Get the configuration of services from environment
        $config = $options + [
            'command_queue_service' => Configuration::get('COMMAND_QUEUE_SERVICE',null, 'discard'),
            'command_store_service' => Configuration::get('COMMAND_STORE_SERVICE',null, 'discard'),
            'command_queue' => Configuration::get('COMMAND_QUEUE',null, 'command-bus'),
        ];

        $this->queueName = $config['command_queue'];

        $this->loggerService = DependencyBuilder::resolve('Psr\Log\LoggerInterface');
        if (!$this->loggerService) {
            if ($this->_environment == 'local' || $this->_environment == 'testing') {
                // <TEST> ONLY
                $this->loggerService = new NullLoggerService();
            } else {
                $this->loggerService = new StackDriverLoggerService();
            }
        }

        // Get the Command Queue Service to use from environment
        // $command_queue_service = Configuration::get('COMMAND_QUEUE_SERVICE',null, 'discard');
        // $this->queueName = Configuration::get('COMMAND_QUEUE',null, 'command-bus');
        // Get the Command Store Service to use from environment
        // $command_store_service = Configuration::get('COMMAND_STORE_SERVICE',null, 'db');

        switch($config['command_store_service']) {
            case 'datastore':
                $this->commandStoreRepository = DependencyBuilder::resolve('Webravo\Persistence\Datastore\Store\DataStoreCommandStore');
                break;
            case 'bigquery':
                $this->commandStoreRepository = DependencyBuilder::resolve('Webravo\Persistence\BigQuery\Store\BigQueryCommandStore');
                break;
            case 'db':
            case 'database':
                $this->commandStoreRepository = DependencyBuilder::resolve('Webravo\Persistence\Eloquent\Store\EloquentCommandStore');
                break;
            default:
                $this->commandStoreRepository = null;
                break;
        }

        // Create the remote and local busses
        // Set-up 3 or 4 layers:
        // - Command Logger
        // - Command Store (optional)
        // - Local Command Dispatcher
        // - Remote Command Dispatcher

        switch($config['command_queue_service']) {
            case 'rabbitmq':
                // $this->commandQueueService = new RabbitMQService();
                $this->commandQueueService = DependencyBuilder::resolve('Webravo\Persistence\Service\RabbitMQService');
                // Initialize the RabbitMQ event-bus
                $this->commandQueueService->createChannel('fanout', $this->queueName);
                $this->commandQueueService->createQueue($this->queueName);
                $this->commandQueueService->subscribeQueue($this->queueName, $this->queueName);
                // Create the command bus
                switch($config['command_store_service']) {
                    case 'datastore':
                    case 'bigquery':
                    case 'db':
                    case 'database':
                        // Create the command bus made of 4 layers
                        $layer4 = new CommandRemoteBusMiddleware(null, $this->commandQueueService);
                        $layer3 = new CommandBusDispatcher($layer4);
                        $layer2 = new CommandBucketBusMiddleware($layer3,$this->commandStoreRepository);
                        $this->commandBus = new CommandLoggerBusMiddleware($layer2, $this->loggerService);
                        break;
                    default:
                        // No command store - create the command bus made of 4 layers
                        $layer3 = new CommandRemoteBusMiddleware(null, $this->commandQueueService);
                        $layer2 = new CommandBusDispatcher($layer3);
                        $this->commandBus = new CommandLoggerBusMiddleware($layer2, $this->loggerService);
                        break;
                }
                break;
            case 'db':
            case 'database':
                // Initialize the DB event-bus
                // $jobQueueService = DependencyBuilder::resolve('Webravo\Infrastructure\Repository\JobQueueInterface');
                $jobQueueService = DependencyBuilder::resolve('Webravo\Persistence\Eloquent\Store\EloquentJobStore');
                $this->commandQueueService = new DBQueueService($jobQueueService);
                // Connect to the command-bus
                $this->commandQueueService->createChannel('fanout', $this->queueName);
                $this->commandQueueService->createQueue($this->queueName);
                $this->commandQueueService->subscribeQueue($this->queueName, $this->queueName);
                $this->commandQueueService->close();
                // Create the command bus
                switch($config['command_store_service']) {
                    case 'datastore':
                    case 'bigquery':
                    case 'db':
                    case 'database':
                        // Create the command bus made of 4 layers
                        $layer4 = new CommandRemoteBusMiddleware(null, $this->commandQueueService);
                        $layer3 = new CommandBusDispatcher($layer4);
                        $layer2 = new CommandBucketBusMiddleware($layer3,$this->commandStoreRepository);
                        $this->commandBus = new CommandLoggerBusMiddleware($layer2, $this->loggerService);
                        break;
                    default:
                        // No command store - create the command bus made of 4 layers
                        $layer3 = new CommandRemoteBusMiddleware(null, $this->commandQueueService);
                        $layer2 = new CommandBusDispatcher($layer3);
                        $this->commandBus = new CommandLoggerBusMiddleware($layer2, $this->loggerService);
                        break;
                }
                break;
            case 'sync':
                $this->commandQueueService = null;
                // Create the command bus
                // Don't use a remote command-bus
                switch($config['command_store_service']) {
                    case 'datastore':
                    case 'bigquery':
                    case 'db':
                    case 'database':
                        // Create the command bus made of 3 layers
                        $layer3 = new CommandBusDispatcher(null);
                        $layer2 = new CommandBucketBusMiddleware($layer3,$this->commandStoreRepository);
                        $this->commandBus = new CommandLoggerBusMiddleware($layer2, $this->loggerService);
                        break;
                    default:
                        // No command store - create the command bus made of 2 layers
                        $layer2 = new CommandBusDispatcher(null);
                        $this->commandBus = new CommandLoggerBusMiddleware($layer2, $this->loggerService);
                        break;
                }
                break;
            case 'discard':
            case 'null':
            default:
                // No commands dispatched
                $this->commandQueueService = new NullQueueService();
                switch($config['command_store_service']) {
                    case 'datastore':
                    case 'bigquery':
                    case 'db':
                    case 'database':
                        // Create the command bus made of 2 layers
                        $layer2 = new CommandBucketBusMiddleware(null,$this->commandStoreRepository);
                        $this->commandBus = new CommandLoggerBusMiddleware($layer2, $this->loggerService);
                        break;
                    default:
                        // No command store - create the command bus made of 1 layer
                        $this->commandBus = new CommandLoggerBusMiddleware(null, $this->loggerService);
                        break;
                }
                break;
        }
    }

    public function registerMapper($mapper, $class_name):void
    {
        if ($this->commandBus) {
            $this->commandBus->subscribeHandlerMapper($mapper, $class_name);
        }
    }

    public function dispatchCommand(CommandInterface $command)
    {
        if ($this->commandBus) {
            return $this->commandBus->dispatch($command);
        }
        return null;
    }

    public function processCommandQueue()
    {
        if ($this->commandQueueService) {
            while(($message = $this->commandQueueService->getSingleMessage($this->queueName)) !== null) {
                $success = $this->CommandCallback($message);
                if ($success) {
                    $this->commandQueueService->messageAcknowledge($message);
                }
                else {
                    $this->commandQueueService->messageNotAcknowledge($message);
                }
            }
        }
        else {
            // Nothing to do ... there is no remote queue to process (COMMAND_QUEUE_SERVICE=sync)
        }
    }

    protected function CommandCallback($message): bool
    {
        if ($this->commandBus) {
            // Rebuild command instance from raw message
            $commandPayload = json_decode($message->body, true);
            if (is_null($commandPayload) || !is_array($commandPayload)) {
                $this->LoggerService->error('Command with empty payload - channel: ' . $message->getChannel() . ' guid: ' . $message->getGuid() . ' created: ' . $message->getCreatedAt());
                return false;
            } else {
                $genericCommand = GenericCommand::buildFromArray($commandPayload);
                if (!$this->localCommandDispatcher) {
                    // Instantiate a local command dispatcher (if not already set)
                    $this->localCommandDispatcher = new CommandBusDispatcher(null);
                }
                $this->localCommandDispatcher->dispatch($genericCommand);
            }
        }
        return true;
    }

    public static function instance($options = [])
    {
        if (null === static::$instance) {
            static::$instance = new static($options);
        }
        return static::$instance;
    }
}
