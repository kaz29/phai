<?php
namespace kaz29\Phai\Tests\ApplicationInsights\Channel;

use kaz29\Phai\ApplicationInsights\Telemetry_Client;
use PHPUnit\Framework\TestCase;
use ApplicationInsights\Channel\Contracts\Message_Severity_Level;

class Telemetry_ClientTest extends TestCase
{
    public function SeverityLevelDataProvider()
    {
        return [
            [
                Message_Severity_Level::VERBOSE,
                [
                    Message_Severity_Level::VERBOSE,
                    Message_Severity_Level::INFORMATION,
                    Message_Severity_Level::WARNING,
                    Message_Severity_Level::ERROR,
                    Message_Severity_Level::CRITICAL,
                ],
            ],
            [
                Message_Severity_Level::INFORMATION,
                [
                    Message_Severity_Level::INFORMATION,
                    Message_Severity_Level::WARNING,
                    Message_Severity_Level::ERROR,
                    Message_Severity_Level::CRITICAL,
                ],
            ],
            [
                Message_Severity_Level::WARNING,
                [
                    Message_Severity_Level::WARNING,
                    Message_Severity_Level::ERROR,
                    Message_Severity_Level::CRITICAL,
                ],
            ],
            [
                Message_Severity_Level::ERROR,
                [
                    Message_Severity_Level::ERROR,
                    Message_Severity_Level::CRITICAL,
                ],
            ],
            [
                Message_Severity_Level::CRITICAL,
                [
                    Message_Severity_Level::CRITICAL,
                ],
            ],
        ];
    }

    /**
     * @dataProvider SeverityLevelDataProvider
     * @param $logLevel
     * @param $results
     */
    public function testServerityLevel($logLevel, $results)
    {
        $client = new Telemetry_Client(null, null, $logLevel);

        $client->trackMessage("VERBOSE", Message_Severity_Level::VERBOSE);
        $client->trackMessage("INFORMATION", Message_Severity_Level::INFORMATION);
        $client->trackMessage("WARNING", Message_Severity_Level::WARNING);
        $client->trackMessage("ERROR", Message_Severity_Level::ERROR);
        $client->trackMessage("CRITICAL", Message_Severity_Level::CRITICAL);

        $result = $client->getChannel()->getQueue();
        $this->assertCount(count($results), $result);

        foreach ($result as $key => $item) {
            $this->assertEquals($results[$key], $item->getData()->getBaseData()->getSeverityLevel());
        }
    }
}