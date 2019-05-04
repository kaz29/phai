<?php

namespace kaz29\Phai\Tests\Logger;

use ApplicationInsights\Channel\Contracts\Message_Severity_Level;
use ApplicationInsights\Telemetry_Client;
use kaz29\Phai\Log\Logger;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    public function loggerTestDataProvider()
    {
        return [
            ['emergency', Message_Severity_Level::CRITICAL],
            ['alert', Message_Severity_Level::ERROR],
            ['critical', Message_Severity_Level::CRITICAL],
            ['error', Message_Severity_Level::ERROR],
            ['warning', Message_Severity_Level::WARNING],
            ['notice', Message_Severity_Level::INFORMATION],
            ['info', Message_Severity_Level::INFORMATION],
            ['debug', Message_Severity_Level::VERBOSE],
        ];
    }

    /**
     * @dataProvider loggerTestDataProvider
     * @group logger
     */
    public function testLogger($method, $logLevel)
    {
        $client = new Telemetry_Client();
        $logger = new Logger($client);

        $data = ['data' => $method];

        $logger->{$method}($method, $data);
        $result = $client->getChannel()->getQueue();
        $result = $result[0]->getData()->getBaseData();

        $this->assertEquals($method, $result->getMessage());
        $this->assertEquals($data, $result->getProperties());
        $this->assertEquals($logLevel, $result->getSeverityLevel());
    }

    /**
     * @dataProvider loggerTestDataProvider
     * @group logger
     */
    public function testLog($method, $logLevel)
    {
        $client = new Telemetry_Client();
        $logger = new Logger($client);

        $data = ['data' => $method];

        $logger->log($method, $method, $data);
        $result = $client->getChannel()->getQueue();
        $result = $result[0]->getData()->getBaseData();

        $this->assertEquals($method, $result->getMessage());
        $this->assertEquals($data, $result->getProperties());
        $this->assertEquals($logLevel, $result->getSeverityLevel());
    }
}