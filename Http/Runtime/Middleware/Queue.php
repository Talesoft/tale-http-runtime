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

    private $_middlewares;
    private $_savedState;
    private $_running;

    public function __construct(array $middlewares = null)
    {

        $this->_running = false;
        $this->_savedState = [];
        $this->_middlewares = $middlewares ?: [];
    }

    public function isRunning()
    {

        return $this->_running;
    }

    public function append($value)
    {

        if (is_array($value)) {

            foreach ($value as $middleware)
                $this->append($middleware);

            return $this;
        }

        $this->_middlewares[] = $value;

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

        array_unshift($this->_middlewares, $value);

        return $this;
    }

    public function count()
    {

        return count($this->_middlewares);
    }

    private function _save()
    {

        $this->_savedState = $this->_middlewares;

        return $this;
    }

    private function _restore()
    {

        $this->_middlewares = $this->_savedState;

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

        if ($this->_running)
            throw new \RuntimeException(
                "Failed to run middleware queue: Queue is already running"
            );

        $this->_running = true;
        $this->_save();

        $next = function (
            ServerRequestInterface $request,
            ResponseInterface $response
        ) use (&$next) {

            if (count($this->_middlewares) > 0) {

                $middleware = array_shift($this->_middlewares);
                $response = call_user_func($middleware, $request, $response, $next);

                if (!($response instanceof ResponseInterface))
                    throw new \RuntimeException(
                        "Failed to run middleware: Middleware didn't return ".
                        "a valid ".ResponseInterface::class." object"
                    );
            }

            $this->_running = false;
            $this->_restore();
            return $response;
        };

        return $next($request, $response);
    }

    protected function handleRequest()
    {

        return $this->handleNext(
            null,
            $this->run($this->getRequest(), $this->getResponse())
        );
    }
}