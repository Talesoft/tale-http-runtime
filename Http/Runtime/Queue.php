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
        parent::push($value);
    }

    public function unshift($value)
    {
        Runtime::validateMiddleware($value);
        parent::unshift($value);
    }

    public function append($value)
    {

        return $this->push($value);
    }

    public function prepend($value)
    {

        return $this->unshift($value);
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

        $next = function (
            ServerRequestInterface $request,
            ResponseInterface $response
        ) use (&$next) {

            if (count($this) < 1)
                return $response;

            $middleware = $this->dequeue();
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