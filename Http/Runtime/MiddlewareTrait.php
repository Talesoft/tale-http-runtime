<?php

namespace Tale\Http\Runtime;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class MiddlewareTrait
 *
 * @package Tale\Http\Runtime
 */
trait MiddlewareTrait
{

    /**
     * @var ServerRequestInterface
     */
    protected $request = null;

    /**
     * @var ResponseInterface
     */
    protected $response = null;

    /**
     * @param callable $next
     *
     * @return ResponseInterface
     */
    protected function handleRequest(callable $next)
    {

        return $next($this->request, $this->response);
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

        $this->request = $request;
        $this->response = $response;
        $response = $this->handleRequest($next);
        $this->request = null;
        $this->response = null;

        return $response;
    }
}