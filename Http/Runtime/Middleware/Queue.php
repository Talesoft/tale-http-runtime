<?php

namespace Tale\Http\Runtime\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tale\Http\Runtime;
use Tale\Http\Runtime\MiddlewareInterface;
use Tale\Http\Runtime\MiddlewareTrait;

/**
 * Class Queue
 *
 * @package Tale\Http\Runtime
 */
class Queue implements MiddlewareInterface, \Countable
{
    use MiddlewareTrait;

    private $middleware;
    private $savedState;
    private $running;

    public function __construct(array $middleware = null)
    {

        $this->running = false;
        $this->savedState = [];
        $this->middleware = $middleware ?: [];
    }

    public function isRunning()
    {

        return $this->running;
    }

    public function append($value)
    {

        if (is_array($value)) {

            foreach ($value as $middleware)
                $this->append($middleware);

            return $this;
        }

        $this->middleware[] = $value;

        return $this;
    }

    public function prepend($value)
    {

        if (is_array($value)) {

            $i = count($value);
            while ($i--)
                $this->prepend($value[$i]);

            return $this;
        }

        array_unshift($this->middleware, $value);

        return $this;
    }

    public function count()
    {

        return count($this->middleware);
    }

    private function save()
    {

        $this->savedState = $this->middleware;

        return $this;
    }

    private function restore()
    {

        $this->middleware = $this->savedState;

        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     */
    public function run(
        ServerRequestInterface $request,
        ResponseInterface $response
    )
    {

        if ($this->running)
            throw new \RuntimeException(
                "Failed to run middleware queue: Queue is already running"
            );

        $this->running = true;
        $this->save();

        $next = function (
            ServerRequestInterface $request,
            ResponseInterface $response
        ) use (&$next) {

            if (count($this->middleware) > 0) {

                $middleware = array_shift($this->middleware);
                $response = call_user_func($middleware, $request, $response, $next);

                if (!($response instanceof ResponseInterface))
                    throw new \RuntimeException(
                        "Failed to run middleware: Middleware didn't return ".
                        "a valid ".ResponseInterface::class." object"
                    );
            }

            $this->running = false;
            $this->restore();
            return $response;
        };

        return $next($request, $response);
    }

    protected function handleRequest(callable $next)
    {

        return $next(
            $this->request,
            $this->run($this->request, $this->response)
        );
    }
}