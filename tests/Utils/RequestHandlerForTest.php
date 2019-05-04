<?php
namespace Phai\Tests\Utils;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class RequestHandlerForTest implements RequestHandlerInterface
{
    private $hook;

    public function __construct($function)
    {
        $this->hook = $function;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return ($this->hook)($request);
    }
}