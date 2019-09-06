<?php
namespace Webravo\Application\Service;

use Webravo\Application\Exception\EventException;
use Webravo\Common\Contracts\EventsQueueServiceInterface;
use Webravo\Application\Event\EventBucketBusMiddleware;
use Webravo\Application\Event\EventBusDispatcher;
use Webravo\Application\Event\EventBusFactory;
use Webravo\Application\Event\EventHandlerInterface;
use Webravo\Application\Event\EventInterface;
use Webravo\Application\Event\EventRemoteBusMiddleware;
use Webravo\Application\Event\GenericEvent;
use Webravo\Persistence\BigQuery\Store\BigQueryEventStore;
use Webravo\Persistence\Service\NullLoggerService;
use Webravo\Persistence\Service\NullQueueService;
use Webravo\Persistence\Service\RabbitMQService;
use Webravo\Persistence\Service\DBQueueService;
use Webravo\Persistence\Service\StackDriverLoggerService;
use Webravo\Persistence\Datastore\Store\DataStoreEventStore;
use Webravo\Persistence\Eloquent\Store\EloquentEventStore;
use Webravo\Infrastructure\Library\Configuration;
use Webravo\Infrastructure\Library\DependencyBuilder;

class EventsQueueService implements EventsQueueServiceInterface
{

    /**
     * Singleton instances array (may be different by options)
     * @var self instance
     */
    private static $instance = [];

    /**
     * Instance of QueueServiceInterface to access the remote event bus queue
     * @var
     */
    protected $eventQueueService;

    /**
     * Array of Instances of QueueServiceInterface to access the remote event bus queues
     * @array
     */
    // protected $a_eventQueueServices = [];

    /**
     * The event bus used to dispatch remote events
     * @var instance of EventBusInterface
     */
    protected $eventBusRemote;

    /**
     * The event bus used to dispatch internal events
     * @var instance of EventBusInterface
     */
    protected $eventBusLocal;

    /**
     * The event store (bucket)
     * @var instance of EventStoreInterface
     */
    protected $eventStoreRepository;

    /**
     * The event bus used to store events in store bucket
     * @var instance of EventBucketBusMiddleware
     */
    protected $eventBusStore;

    /**
     * The remote exchange name
     * @var
     */
    protected $exchangeName;

    /**
     * The remote queue name 
     * @var 
     */
    protected $queueName;

    /**
     * The remote exchange mode (fanaout/topic/direct)
     * @var
     */
    protected $exchangeMode;

    /**
     * The subscribed topic
     * @var
     */
    protected $topic;

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
            'event_queue_service' => Configuration::get('EVENT_QUEUE_SERVICE',null, 'discard'),
            'event_store_service' => Configuration::get('EVENT_STORE_SERVICE',null, 'discard'),
            'event_exchange_name' => Configuration::get('EVENT_EXCHANGE_NAME',null, 'event-bus'),
            'event_queue' => Configuration::get('EVENT_QUEUE',null, 'event-bus'),
            'event_exchange_mode' => Configuration::get('EVENT_EXCHANGE_MODE',null, 'fanout'),
            'event_topic' => Configuration::get('EVENT_TOPIC',null, ''),
        ];


        $this->exchangeName = $config['event_exchange_name'];
        $this->queueName = $config['event_queue'];
        $this->exchangeMode = $config['event_exchange_mode'];
        $this->topic = $config['event_topic'];

        if ($this->_environment == 'local' || $this->_environment == 'testing') {
            // <TEST> ONLY
            $this->loggerService = new NullLoggerService();
        }
        else {
            $this->loggerService = new StackDriverLoggerService();
        }

        // Get the Event Queue Service to use from environment
        // $event_queue_service = Configuration::get('EVENT_QUEUE_SERVICE',null, 'null');
        // $this->queueName = Configuration::get('EVENT_QUEUE',null, 'event-bus');
        // Get the Event Store Service to use from environment
        // $event_store_service = Configuration::get('EVENT_STORE_SERVICE',null, 'db');


        switch($config['event_store_service']) {
            case 'datastore':
                $this->eventStoreRepository = new DataStoreEventStore();
                $this->eventBusStore = new EventBucketBusMiddleware(null, $this->eventStoreRepository);
                break;
            case 'bigquery':
                $this->eventStoreRepository = new BigQueryEventStore();
                $this->eventBusStore = new EventBucketBusMiddleware(null, $this->eventStoreRepository);
                break;
            case 'db':
            case 'database':
                $this->eventStoreRepository = new EloquentEventStore();
                $this->eventBusStore = new EventBucketBusMiddleware(null, $this->eventStoreRepository);
                break;
            default:
                break;
                $this->eventStoreRepository = null;
                $this->eventBusStore = null;
        }

        // Create the remote and local busses
        // Set-up 3 layers:
        // - Events Store                   ($this->eventBusStore)
        // - Remote Events Dispatcher       ($this->eventBusRemote)
        // - Local Events Dispatcher        ($this->eventBusDispatcher)

        switch($config['event_queue_service']) {
            case 'rabbitmq':
                $this->eventQueueService = new RabbitMQService();
                // Initialize the RabbitMQ event-bus
                $this->eventQueueService->createChannel($this->exchangeMode, $this->exchangeName);
                $this->eventQueueService->createQueue($this->queueName);
                $this->eventQueueService->subscribeQueue($this->queueName, $this->exchangeName, $this->topic);
                // Create the remote and local busses
                $this->eventBusRemote = new EventRemoteBusMiddleware($this->eventBusStore, $this->eventQueueService);
                $this->eventBusLocal = EventBusDispatcher::instance();
                break;
            case 'db':
            case 'database':
                // Initialize the DB event-bus
                $jobQueueService = DependencyBuilder::resolve('Webravo\Infrastructure\Repository\JobQueueInterface');
                $this->eventQueueService = new DBQueueService($jobQueueService);
                // Connect to the event-bus
                $this->eventQueueService->createChannel($this->exchangeMode, $this->exchangeName);
                $this->eventQueueService->createQueue($this->queueName);
                $this->eventQueueService->subscribeQueue($this->queueName, $this->exchangeName, $this->topic);
                $this->eventQueueService->close();
                // Create the remote and local busses
                $this->eventBusRemote = new EventRemoteBusMiddleware($this->eventBusStore, $this->eventQueueService);
                $this->eventBusLocal = EventBusDispatcher::instance();
                break;
            case 'sync':
                $this->eventQueueService = null;
                // Use a single event-bus as remote and local
                $this->eventBusLocal = EventBusDispatcher::instance();
                if (!is_null($this->eventBusStore)) {
                    // Need an events store
                    $this->eventBusRemote = new EventBucketBusMiddleware($this->eventBusLocal, $this->eventStoreRepository);
                }
                else {
                    // Remote and Local bus are joined together
                    $this->eventBusRemote = $this->eventBusLocal;
                }
                break;
            case 'discard':
            case 'null':
            default:
                // No events dispatched
                $this->eventQueueService = new NullQueueService();
                $this->eventBusLocal = null;
                if (!is_null($this->eventBusStore)) {
                    // Events are still stored
                    $this->eventBusRemote = new EventBucketBusMiddleware(null, $this->eventStoreRepository);
                }
                else {
                    $this->eventBusRemote = null;
                }
                break;
        }

        if ($this->eventBusLocal) {
            // Read event handlers list from configuration
            $handlers = Configuration::getClass('domain-events');
            // Bind event handlers to local event-bus
            foreach ($handlers as $handler) {
                try {
                    $this->eventBusLocal->subscribe($handler);
                }
                catch (EventException $e) {
                    // Ignore any handler that cannnot be registered correctly
                    $this->loggerService->warning($e->getMessage());
                }
            }
        }
    }

    public function dispatchEvent(EventInterface $event, $topic = null):void
    {
        $this->loggerService->debug('Dispatch event ' . $event->getType());

        // Dispatch to remote bus
        if ($this->eventBusRemote) {
            $this->eventBusRemote->dispatch($event, $topic);
        }
        if ($this->eventBusLocal && $this->eventBusLocal !== $this->eventBusRemote) {
            $this->eventBusLocal->dispatch($event, $topic);
        }
    }

    public function registerHandler($handler):void
    {
        if ($this->eventBusRemote) {
            $this->eventBusRemote->subscribe($handler);
        }
        if ($this->eventBusLocal) {
            $this->eventBusLocal->subscribe($handler);
        }
    }

    public function processEventsQueue()
    {
        if ($this->eventQueueService) {
            while(($message = $this->eventQueueService->getSingleMessage($this->queueName)) !== null) {
                $success = $this->EventCallback($message);
                if ($success) {
                    $this->eventQueueService->messageAcknowledge($message);
                }
                else {
                    $this->eventQueueService->messageNotAcknowledge($message);
                }
            }
        }
        else {
            // Nothing to do ... there is no remote queue to process (EVENT_QUEUE_SERVICE=sync)
        }
    }

    protected function EventCallback($message): bool
    {
        if ($this->eventBusLocal) {
            // Rebuild event instance from raw message
            $eventPayload = json_decode($message->body, true);
            if (is_null($eventPayload) || !is_array($eventPayload)) {
                $this->LoggerService->error('Event with empty payload - channel: ' . $message->getChannel() . ' guid: ' . $message->getGuid() . ' created: ' . $message->getCreatedAt());
                return false;
            } else {
                $genericEvent = GenericEvent::buildFromArray($eventPayload);
                $this->eventBusLocal->dispatch($genericEvent);
            }
        }
        return true;
    }

    public static function instance($options = [])
    {
        $hash_options = md5(serialize($options));
        if (!is_array(static::$instance) || !isset(static::$instance[$hash_options])) {
            static::$instance[$hash_options] = new static($options);
        }
        return static::$instance[$hash_options];
    }

}
