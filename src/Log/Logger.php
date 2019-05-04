<?php
namespace kaz29\Phai\Log;

use ApplicationInsights\Channel\Contracts\Message_Severity_Level;
use ApplicationInsights\Telemetry_Client;
use Psr\Log\LoggerInterface;

class Logger implements LoggerInterface
{
    /**
     * @var Telemetry_Client
     */
    private $client;

    public function __construct(Telemetry_Client $client)
    {
        $this->client = $client;
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function emergency($message, array $context = array())
    {
        $this->critical($message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function alert($message, array $context = array())
    {
        $this->error($message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function critical($message, array $context = array())
    {
        $this->client->trackMessage($message, Message_Severity_Level::CRITICAL, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function error($message, array $context = array())
    {
        $this->client->trackMessage($message, Message_Severity_Level::ERROR, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function warning($message, array $context = array())
    {
        $this->client->trackMessage($message, Message_Severity_Level::WARNING, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function notice($message, array $context = array())
    {
        $this->info($message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function info($message, array $context = array())
    {
        $this->client->trackMessage($message, Message_Severity_Level::INFORMATION, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function debug($message, array $context = array())
    {
        $this->client->trackMessage($message, Message_Severity_Level::VERBOSE, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        switch(strtolower($level)) {
            case 'info':
                $this->info($message, $context);
                break;
            case 'notice':
                $this->notice($message, $context);
                break;
            case 'warning':
                $this->warning($message, $context);
                break;
            case 'error':
                $this->error($message, $context);
                break;
            case 'critical':
                $this->critical($message, $context);
                break;
            case 'alert':
                $this->alert($message, $context);
                break;
            case 'emergency':
                $this->emergency($message, $context);
                break;
            case 'debug':
            default:
                $this->debug($message, $context);
                break;
        }
    }
}
