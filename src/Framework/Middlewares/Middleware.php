<?php
/**
 * Middleware.php.
 *
 * @author   Edwin Dayot
 */

namespace Shorty\Framework\Middlewares;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Shorty\Framework\Middlewares\Contracts\MiddlewareInterface;
use Shorty\Framework\Middlewares\Contracts\RequestHandlerInterface;

/**
 * Class Middleware.
 */
class Middleware implements MiddlewareInterface
{
    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @param RequestInterface        $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(
        RequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        return $handler->handle($request);
    }
}
