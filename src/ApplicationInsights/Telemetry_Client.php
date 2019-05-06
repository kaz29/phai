<?php
namespace kaz29\Phai\ApplicationInsights;

use ApplicationInsights\Channel\Contracts\Severity_Level;
use ApplicationInsights\Telemetry_Context;
use ApplicationInsights\Channel\Telemetry_Channel;
use ApplicationInsights\Channel\Contracts\Message_Data;

class Telemetry_Client extends \ApplicationInsights\Telemetry_Client
{
    private $logLevel;

    /**
     * Initializes a new Telemetry_Client.
     * @param \ApplicationInsights\Telemetry_Context $context
     * @param \ApplicationInsights\Channel\Telemetry_Channel $channel
     */
    public function __construct(Telemetry_Context $context = NULL, Channel\Telemetry_Channel $channel = NULL, $logLevel = Severity_Level::Verbose)
    {
        parent::__construct($context, $channel);

        $this->logLevel = $logLevel;
    }

    /**
     * Sends an Message_Data to the Application Insights service.
     * @param string $message The trace message.
     * @param string $severityLevel The severity level of the message. Found: \ApplicationInsights\Channel\Contracts\Message_Severity_Level::Value
     * @param array $properties An array of name to value pairs. Use the name as the index and any string as the value.
     */
    public function trackMessage($message, $severityLevel = NULL, $properties = NULL)
    {
        if ($this->logLevel > $severityLevel) {
            return;
        }

        $data = new Message_Data();
        $data->setMessage($message);
        $data->setSeverityLevel($severityLevel);

        if ($properties != NULL)
        {
            $data->setProperties($properties);
        }

        $this->getChannel()->addToQueue($data, $this->getContext());
    }
}