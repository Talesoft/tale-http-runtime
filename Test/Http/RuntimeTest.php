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

    public function testPrepending()
    {

        $queue = new Queue();
        $queue->enqueue(function($req, $res, $next) use ($queue) {

            $queue->prepend(function($req, $res, $next) {

                $res->getBody()->write('Second!');
                return $next($req, $res);
            });

            $res->getBody()->write('First!');
            return $next($req, $res);
        });
        $queue->enqueue(function($req, $res, $next) use ($queue) {

            $queue->append(function($req, $res, $next) {

                $res->getBody()->write('Sixth!');
                return $next($req, $res);
            });

            $res->getBody()->write('Third!');
            return $next($req, $res);
        });
        $queue->enqueue(function($req, $res, $next) use ($queue) {

            $queue->prepend(function($req, $res, $next) {

                $res->getBody()->write('Fifth!');
                return $next($req, $res);
            });

            $res->getBody()->write('Fourth!');
            return $next($req, $res);
        });

        $this->assertEquals('First!Second!Third!Fourth!Fifth!Sixth!',
            (string)Runtime::dispatch($queue)->getBody()
        );
    }
}