<?php

namespace Tale\Http\Runtime;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SplQueue;
use Tale\Http\Runtime;

/**
 * Class Queue
 * @package Tale\Http\Runtime
 */
class Queue extends SplQueue implements MiddlewareInterface
{

    public function add($index, $value)
    {

        Runtime::validateMiddleware($value);
        parent::add($index, $value);
    }

    public function push($value)
    {

        Runtime::validateMiddleware($value);
        parent::push($value); //
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     */
    public function dispatch(
        ServerRequestInterface $request,
        ResponseInterface $response
    )
    {

        $queue = clone $this;

        $next = function (
            ServerRequestInterface $request,
            ResponseInterface $response
        ) use (&$next, $queue) {

            if (count($queue) < 1)
                return $response;

            $middleware = $queue->dequeue();
            $response = call_user_func($middleware, $request, $response, $next);

            if (!($response instanceof ResponseInterface))
                throw new \RuntimeException(
                    "Failed to run middleware: Middleware didn't return ".
                    "a valid ".ResponseInterface::class." object"
                );

            return $response;
        };

        return $next($request, $response);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param callable               $next
     *
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    )
    {

        return $next($request, $this->dispatch($request, $response));
    }
}