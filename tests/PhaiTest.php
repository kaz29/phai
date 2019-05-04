<?php
namespace kaz29\Phai\Tests;

use ApplicationInsights\Channel\Contracts\Application;
use ApplicationInsights\Channel\Contracts\Envelope;
use ApplicationInsights\Channel\Contracts\Message_Severity_Level;
use ApplicationInsights\Channel\Contracts\User;
use ApplicationInsights\Telemetry_Client;
use ApplicationInsights\Telemetry_Context;
use kaz29\Phai\Phai;
use PHPUnit\Framework\TestCase;

class PhaiTest extends TestCase
{
    public function testInstance()
    {
        $app = new Application();
        $app->setVer('1.0.1');
        $user = new User();
        $user->setId(10);
        $instrumentationKey = 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx';

        // Create mock for Telemetry_Context
        $context = $this->getMockBuilder(Telemetry_Context::class)
            ->setMethods(['setInstrumentationKey', 'setApplicationContext', 'setUserContext'])
            ->getMock();
        $context->expects($this->once())
            ->method('setInstrumentationKey')
            ->with(
                $this->equalTo($instrumentationKey)
            );
        $context->expects($this->once())
            ->method('setApplicationContext')
            ->with(
                $this->equalTo($app)
            );
        $context->expects($this->once())
            ->method('setUserContext')
            ->with(
                $this->equalTo($user)
            );

        /**
         * Create mock for Telemetry_Client
         * @var $client Telemetry_Client
         */
        $client = $this->getMockBuilder(Telemetry_Client::class)
            //->setMethods(['flush', 'trackMessage', 'getContext'])
            ->setMethods(['flush', 'getContext'])
            ->getMock();

        $client->expects($this->any())
            ->method('getContext')
            ->willReturn($context);
        $client->expects($this->once())
            ->method('flush');

        $result = Phai::initialize($client, $instrumentationKey, $app, $user);
        $this->assertInstanceOf('\ApplicationInsights\Telemetry_Client', $result);

        $client = Phai::getClient();

        // Simulate register_shutdown_function call.
        Phai::shutdown();

        /**
         * @var $result Envelope[]
         */
        $result = $client->getChannel()->getQueue();
        $this->assertCount(2, $result);

        $baseData = $result[0]->getData()->getBaseData();
        $this->assertEquals(Message_Severity_Level::VERBOSE, $baseData->getSeverityLevel());
        $this->assertEquals('initialized', $baseData->jsonSerialize()['message']);

        $baseData = $result[1]->getData()->getBaseData();
        $this->assertEquals(Message_Severity_Level::VERBOSE, $baseData->getSeverityLevel());
        $this->assertEquals('register_shutdown_function', $result[1]->getData()->getBaseData()->jsonSerialize()['message']);
    }
}
