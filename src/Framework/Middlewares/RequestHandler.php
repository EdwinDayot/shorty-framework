<?php
/**
 * RequestHandler.php.
 *
 * @author   Edwin Dayot
 */

namespace Shorty\Framework\Middlewares;

use DI\Container;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Shorty\Framework\Middlewares\Contracts\RequestHandlerInterface;

/**
 * Class RequestHandler.
 */
class RequestHandler implements RequestHandlerInterface
{
    /**
     * Index of middleware.
     *
     * @var int
     */
    private $index = 0;

    /**
     * Container object.
     *
     * @var \DI\Container
     */
    private $container;

    /**
     * Middlewares array.
     *
     * @var \Shorty\Framework\Middlewares\Contracts\MiddlewareInterface[]
     */
    private $middlewares;

    /**
     * Response object.
     *
     * @var \GuzzleHttp\Psr7\Response
     */
    private $response;

    /**
     * RequestHandler constructor.
     *
     * @param \DI\Container                                                 $container
     * @param \Shorty\Framework\Middlewares\Contracts\MiddlewareInterface[] $middlewares
     */
    public function __construct(Container $container, array $middlewares = [])
    {
        $this->container = $container;
        $this->middlewares = $middlewares;
        $this->response = new Response(200);
    }

    /**
     * Handle the request and return a response.
     *
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(RequestInterface $request): ResponseInterface
    {
        if (!array_key_exists($this->index, $this->middlewares)) {
            return $this->response;
        }

        $current = $this->index;

        ++$this->index;

        $response = $this->container->get($this->middlewares[$current])->process($request, $this);

        return $response;
    }
}
