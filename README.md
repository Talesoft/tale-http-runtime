
# Tale Http Runtime
**A Tale Framework Component**

# What is Tale Http Runtime?


It is PSR-7 compliant

# Installation

Install via Composer

```bash
composer require "talesoft/tale-http-runtime:*"
composer install
```

# Usage

```php

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


$queue = new Queue();
$queue->append(new HelloMiddleware())
      ->append(new WorldMiddleware())
      ->append(new FuckingMiddleware());

Runtime::emit($queue); //(Output) "Hello fucking World!"
    
```
