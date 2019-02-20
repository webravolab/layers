<?php

namespace Webravo\Persistence\Service;

use Symfony\Component\Debug\Exception\FatalThrowableError;
use Webravo\Infrastructure\Library\Configuration;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Google\Cloud\Logging\LoggingClient;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class StackDriverLoggerService extends AbstractProcessingHandler implements LoggerInterface
{
    /*
     * @var array
     */
    protected $options;

    /**
     * @param string $googleProjectId Google Project Id
     * @param string $logName         The name of the log to write entries to.
     * @param array $options          Configuration options.
     * @param object $logging         Logging Object(for test)
     */
    public function __construct($googleProjectId = null, $logName = null, $options=[], $logging=null)
    {
        if (is_null($googleProjectId)) {
            $googleProjectId = Configuration::get('GOOGLE_PROJECT_ID');
        }
        if (is_null($logName)) {
            $logName = Configuration::get('GOOGLE_LOG_NAME', null, 'global');
        }
        if (is_null($logging)) {
            $logging = new LoggingClient([
                'projectId' => $googleProjectId,
            ]);
        }
        $this->logger = $logging->logger($logName);
        // set logger options.
        // see http://googlecloudplatform.github.io/google-cloud-php/#/

        $this->options = [
            'resource' => [
                'type' => 'generic_task',
            ],
            'labels' => [
                'project_id' => $googleProjectId,
                'logName' => $logName,
            ],
            'timestamp' => (new \DateTime())->format(\DateTime::RFC3339),
        ];

        $this->options = $this->array_merge_recursive_distinct($this->options, $options);

    }

    public function write(array $record)
    {
        if (!isset($record['formatted']) || 'string' !== gettype($record['formatted']))
        {
            throw new \InvalidArgumentException('StackdriverLoggerService accepts only formatted records as a string');
        }
        if (!isset($this->options['severity']) && isset($record['level'])) {
            // Add severity if not already set and passed through the record (Monolog compatibility)
            $this->options['severity'] = $record['level'];
        }
        // Update timestamp
        $this->options['timestamp'] = (new \DateTime())->format(\DateTime::RFC3339);
        // Check for any exception to log
        if (isset($this->options['labels']['exception'])) {
            $e = $this->options['labels']['exception'];
            $a_stack = [];
            if ($e instanceof FatalThrowableError) {
                $this->options['labels']['exception'] = $e->getMessage();
                $this->logger->write($record['formatted'], $this->options);
                $trace_message = $e->getFile() . ' - ' . $e->getLine() . ' - ' . $e->getTraceAsString();
                $this->logger->write($trace_message, $this->options);
            }
        }
        else {
            $this->logger->write($record['formatted'], $this->options);
        }
    }

    public function log($severity, $message, array $context = array())
    {
        $level = Logger::getLevelName($severity);
        if (count($context) > 0) {
            $this->options = array_replace($this->options, [
                'labels' => $context
            ]);
        }
        $this->options = array_replace($this->options, [
            'severity' => $severity
        ]);
        $this->write(['formatted' => "[$level]: $message"]);
    }

    public function alert($message, array $context = array())
    {
        $this->log(Logger::ALERT, $message, $context);
    }

    public function critical($message, array $context = array())
    {
        $this->log(Logger::CRITICAL, $message, $context);
    }

    public function debug($message, array $context = array())
    {
        $this->log(Logger::DEBUG, $message, $context);
    }

    public function emergency($message, array $context = array())
    {
        $this->log(Logger::EMERGENCY, $message, $context);
    }

    public function error($message, array $context = array())
    {
        $this->log(Logger::ERROR, $message, $context);
    }

    public function info($message, array $context = array())
    {
        $this->log(Logger::INFO, $message, $context);
    }

    public function notice($message, array $context = array())
    {
        $this->log(LOgger::NOTICE, $message, $context);
    }

    public function warning($message, array $context = array())
    {
        $this->log(Logger::WARNING, $message, $context);
    }


    protected function array_merge_recursive_distinct(array &$array1, array &$array2)
    {
        $merged = $array1;
        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->array_merge_recursive_distinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }
        return $merged;
    }
}