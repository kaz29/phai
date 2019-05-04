<?php
namespace kaz29\Phai\Middleware;

use ApplicationInsights\Telemetry_Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApplicationInsightsMiddleware implements MiddlewareInterface
{
    /**
     * @var string track name
     */
    private $name;

    /**
     * @var Telemetry_Client
     */
    private $client;

    /**
     * Start time for Current Request
     *
     * @var float startTime
     */
    private $startTime;

    public function __construct(Telemetry_Client $client, string $name = 'request')
    {
        $this->startTime = microtime(true);
        $this->name = $name;
        $this->client = $client;
    }

    /**
     * Send telemetry to Application Insights.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            /**
             * @var ResponseInterface $response
             */
            $response = $handler->handle($request);

            $statusMode = (int)($response->getStatusCode()/100);
            $this->client->trackRequest(
                $this->name,
                (string)$request->getUri(),
                $this->startTime,
                $this->getDuration(),
                $response->getStatusCode(),
                ($statusMode === 2 || $statusMode === 3)
            );

            return $response;
        } catch (\Throwable $exception) {
            $this->client->trackException($exception);
            throw $exception;
        } catch (\Exception $exception) {
            $this->client->trackException($exception);
            throw $exception;
        }
    }

    /**
     * Calculate request duration in millisecond.
     *
     * @return int
     */
    private function getDuration(): int
    {
        $endTime = ceil(microtime(true)*1000);
        return (int)($endTime - ceil($this->startTime*1000));
    }
}
