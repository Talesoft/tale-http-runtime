<?php

namespace Tale\Test\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tale\Http\Runtime;
use Tale\Http\Runtime\MiddlewareInterface;
use Tale\Http\Runtime\Queue;

class HelloMiddleware implements MiddlewareInterface
{

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    )
    {
        $response->getBody()->write('Hello ');

        return $next($request, $response);
    }
}

class WorldMiddleware implements MiddlewareInterface
{

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    )
    {
        $response = $next($request, $response);
        $response->getBody()->write('World!');
        return $response;
    }
}

class FuckingMiddleware implements MiddlewareInterface
{

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    )
    {
        $response->getBody()->write('fucking ');
        return $next($request, $response);
    }
}

class RuntimeTest extends \PHPUnit_Framework_TestCase
{

    public function testQueue()
    {

        $queue = new Queue();
        $queue->enqueue(new HelloMiddleware());
        $queue->enqueue(new WorldMiddleware());
        $queue->enqueue(new FuckingMiddleware());

        $this->assertEquals('Hello fucking World!',
            (string)Runtime::dispatch($queue)->getBody()
        );
    }
}