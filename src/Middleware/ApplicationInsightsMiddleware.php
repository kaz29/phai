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

    /**
     * Option parameters
     *
     * @var array options
     */
    private $options = [];

    public function __construct(Telemetry_Client $client, string $name = 'request', array $options = [])
    {
        $this->startTime = microtime(true);
        $this->name = $name;
        $this->client = $client;
        $this->options = $options;
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
            $context = $this->client->getContext();
            $serverParams = $request->getServerParams();
            $context->getLocationContext()->setIp($this->getRemoteIP($request));
            $context->setProperties([
                'REMOTE_IP' => $this->getRemoteIP($request),
                'REMOTE_PORT' => $serverParams['REMOTE_PORT'],
                'HTTP_USER_AGENT' => $serverParams['HTTP_USER_AGENT'],
                'METHOD' => $request->getMethod(),
                'PATH' => $request->getUri()->getPath(),
            ]);
            $context->getDeviceContext()->setOsVersion(array_key_exists('HTTP_SEC_CH_UA_PLATFORM', $serverParams) ? $serverParams['HTTP_SEC_CH_UA_PLATFORM'] : 'unknown');
            $context->getDeviceContext()->setModel(array_key_exists('HTTP_SEC_CH_UA', $serverParams) ? $serverParams['HTTP_SEC_CH_UA'] : 'unknown');

            /**
             * @var ResponseInterface $response
             */
            $response = $handler->handle($request);

            if ($this->isExcludePath($request) !== true) {
                $statusMode = (int)($response->getStatusCode()/100);
                $this->client->trackRequest(
                    $this->name,
                    (string)$request->getUri(),
                    $this->startTime,
                    $this->getDuration(),
                    $response->getStatusCode(),
                    ($statusMode === 2 || $statusMode === 3)
                );
            }

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
     * Get remote client ip address from request
     * 
     * @param ServerRequestInterface $request
     * @return string
     */
    protected function getRemoteIP(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();

        return array_key_exists('HTTP_X_FORWARDED_FOR', $serverParams) ? $serverParams['HTTP_X_FORWARDED_FOR'] : $serverParams['REMOTE_ADDR'];
    }

    /**
     * Check if the accessed URI is outside the scope of logging.
     * 
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function isExcludePath(ServerRequestInterface $request): bool
    {
        if (array_key_exists('exclude_paths', $this->options) !== true) {
            return false;
        }

        $uri = parse_url($request->getUri(), PHP_URL_PATH);

        $result = in_array($uri, $this->options['exclude_paths']) || in_array($uri, array_keys($this->options['exclude_paths']));
        if ($result === false) {
            return false;
        }

        if (!array_key_exists($uri, $this->options['exclude_paths']) ||
            !is_array($this->options['exclude_paths'][$uri]) ||
            !array_key_exists('agents', $this->options['exclude_paths'][$uri])) {
            return $result;
        }

        $serverParams = $request->getServerParams();

        return in_array($serverParams['HTTP_USER_AGENT'], $this->options['exclude_paths'][$uri]['agents']);
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
