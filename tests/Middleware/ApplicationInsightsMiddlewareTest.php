<?php
namespace kaz29\Phai\Tests\Middleware;

use ApplicationInsights\Channel\Contracts\Request_Data;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Uri;
use kaz29\Phai\Middleware\ApplicationInsightsMiddleware;
use Phai\Tests\Utils\RequestHandlerForTest;
use PHPUnit\Framework\TestCase;
use ApplicationInsights\Telemetry_Client;
use Psr\Http\Message\ServerRequestInterface;

class ApplicationInsightsMiddlewareTest extends TestCase
{
    private function convertTimeSpanToMilliseconds($timespan)
    {
        list($secstr, $millisecondstr) = explode('.', $timespan);
        $sec = explode(':', $secstr);

        $milliseconds = (int)$millisecondstr;
        $milliseconds += (int)$sec[2]*1000;
        $milliseconds += (int)$sec[1]*(60*1000);
        $milliseconds += (int)$sec[0]*(60*60*1000);

        return $milliseconds;
    }

    public function requestDataProvider()
    {
        return [
            [
                null,
                1,
                200,
                'http://example.com/test0',
                true,
            ],
            [
                '201',
                10,
                201,
                'http://example.com/test1',
                true,
            ],
            [
                '301',
                10,
                301,
                'http://example.com/test1',
                true,
            ],
            [
                '404',
                10,
                404,
                'http://example.com/test2',
                false,
            ],
            [
                '500',
                1000,
                500,
                'http://example.com/test2',
                false,
            ],
        ];
    }

    /**
     * @dataProvider requestDataProvider
     * @group middleware
     * @throws \Throwable
     */
    public function testRequest($name, $sleep, $responseCode, $uri, $success)
    {
        $client = new Telemetry_Client();
        if (is_null($name)) {
            $middleware = new ApplicationInsightsMiddleware($client);
        } else {
            $middleware = new ApplicationInsightsMiddleware($client, $name);
        }

        $handler = new RequestHandlerForTest(function (ServerRequestInterface $request) use($sleep, $responseCode) {
            usleep($sleep*1000);
            return new Response($responseCode);
        });

        $request = new ServerRequest('GET', new Uri($uri));

        $response = $middleware->process($request, $handler);
        $this->assertInstanceOf('\GuzzleHttp\Psr7\Response', $response);

        $result = $client->getChannel()->getQueue();
        $this->assertCount(1, $result);

        /**
         * @var $result Request_Data
         */
        $result = $result[0]->getData()->getBaseData();
        $this->assertInstanceOf('\ApplicationInsights\Channel\Contracts\Request_Data', $result);

        $this->assertEquals(is_null($name)?'request':$name, $result->getName());
        $this->assertEquals($responseCode, $result->getResponseCode());
        $this->assertEquals($success, $result->getSuccess());
        $this->assertEquals($uri, $result->getUrl());
        $this->assertTrue($this->convertTimeSpanToMilliseconds($result->getDuration())>=$sleep);
    }
}