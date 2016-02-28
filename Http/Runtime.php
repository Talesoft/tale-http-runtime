<?php

namespace Tale\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tale\Http;
use Tale\Http\Runtime\Middleware\Queue;

class Runtime
{

    private function __construct() {}

    public static function isMiddleware($value)
    {

        return is_callable($value);
    }

    public static function validateMiddleware($value)
    {

        if (!self::isMiddleware($value))
            throw new \InvalidArgumentException(
                "Passed value is not a valid middleware"
            );
    }

    public static function run(
        Queue $queue,
        ServerRequestInterface $request = null,
        ResponseInterface $response = null
    )
    {

        return $queue->run(
            $request ?: Http::getServerRequest(),
            $response ?: new Response()
        );
    }

    public static function emit(
        Queue $queue,
        ServerRequestInterface $request = null,
        ResponseInterface $response = null
    )
    {

        Http::emit(self::run($queue, $request, $response));
    }
}