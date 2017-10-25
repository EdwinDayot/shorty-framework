<?php
/**
 * MiddlewareInterface.php.
 *
 * @author   Edwin Dayot
 */

namespace Shorty\Framework\Middlewares\Contracts;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * An HTTP middleware component participates in processing an HTTP message,
 * either by acting on the request or the response. This interface defines the
 * methods required to use the middleware.
 */
interface MiddlewareInterface
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
    ): ResponseInterface;
}
