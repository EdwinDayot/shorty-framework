<?php
/**
 * Router.php.
 *
 * @author Edwin Dayot
 */

namespace Shorty\Framework\Routing;

use DI\Container;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Shorty\Framework\Routing\Exceptions\HTTPException;

/**
 * Class Router.
 */
class Router
{
    private $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'PATCH' => [],
        'DELETE' => [],
    ];

    /**
     * Request object.
     *
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    private $request;

    /**
     * Container object.
     *
     * @var \DI\Container
     */
    private $container;

    /**
     * The pending action.
     *
     * @var null
     */
    private $pendingAction = null;

    /**
     * Router constructor.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @param \DI\Container                            $container
     */
    public function __construct(RequestInterface $request, Container $container)
    {
        $this->request = $request;
        $this->container = $container;
    }

    /**
     * Handle the route.
     *
     * @throws \Shorty\Framework\Routing\Exceptions\HTTPException
     *
     * @return bool
     */
    public function build()
    {
        $uri = $this->trimTrailingSlashes($this->request->getUri()->getPath());
        $method = mb_strtoupper($this->request->getMethod());

        foreach ($this->routes[$method] as $routeUri => $action) {
            $arguments = $this->match($uri, $routeUri);

            if (!is_null($arguments)) {
                return $this->constructAction($action, $arguments);
            }
        }

        throw new HTTPException('Not found', 404);
    }

    /**
     * Run the pending action.
     *
     * @return Response
     */
    public function run(): Response
    {
        return $this->triggerAction($this->pendingAction);
    }

    /**
     * Get method.
     *
     * @param string $uri
     * @param $action
     *
     * @return \Shorty\Framework\Routing\Router
     */
    public function get(string $uri, $action): self
    {
        $this->routes['GET'][$this->trimTrailingSlashes($uri)] = $action;

        return $this;
    }

    /**
     * Get method.
     *
     * @param string $uri
     * @param $action
     *
     * @return \Shorty\Framework\Routing\Router
     */
    public function post(string $uri, $action): self
    {
        $this->routes['POST'][$this->trimTrailingSlashes($uri)] = $action;

        return $this;
    }

    /**
     * Trims trailing slashes.
     *
     * @param string $uri
     *
     * @return string
     */
    private function trimTrailingSlashes(string $uri): string
    {
        if ($uri === '/') {
            return $uri;
        }

        return '/' . trim($uri, '/');
    }

    /**
     * Triggers an action.
     *
     * @param $action
     *
     * @throws \Shorty\Framework\Routing\Exceptions\HTTPException
     *
     * @return \GuzzleHttp\Psr7\Response
     */
    private function triggerAction($action): Response
    {
        if (is_callable($action)) {
            return $this->returnResponse(
                $this->container->call($action)
            );
        } elseif (is_array($action)) {
            return $this->returnResponse(
                $this->container->call(
                    [
                        $action['controller'],
                        $action['method'],
                    ],
                    $action['arguments']
                )
            );
        }

        throw new \InvalidArgumentException('Route\'s action could not be handled.');
    }

    /**
     * Returns a Response object.
     *
     * @param mixed $result
     *
     * @return \GuzzleHttp\Psr7\Response|string
     */
    private function returnResponse($result): Response
    {
        if ($result instanceof Response) {
            return $result;
        }

        if (is_array($result)) {
            $result = json_encode($result);
        }

        return new Response(200, [], $result);
    }

    /**
     * Matches the URI and route.
     *
     * @param string $uri
     * @param string $routeUri
     *
     * @return array|null
     */
    private function match(string $uri, string $routeUri): ?array
    {
        if ($uri === $routeUri) {
            return [];
        }

        return $this->getArguments($uri, $routeUri);
    }

    /**
     * Construct action before running it.
     *
     * @param $action
     * @param array $arguments
     *
     * @throws \Shorty\Framework\Routing\Exceptions\HTTPException
     *
     * @return bool
     */
    private function constructAction($action, array $arguments = []): bool
    {
        if (is_callable($action)) {
            $this->pendingAction = $action;

            return true;
        } elseif (is_string($action)) {
            $actionParts = explode('@', $action);

            $controllerName = 'App\\Http\\Controllers\\' . $actionParts[0];
            $methodName = $actionParts[1];

            if (!class_exists($controllerName)) {
                throw new HTTPException('Not found', 404);
            }

            $controller = $this->container->get($controllerName);

            if (!method_exists($controller, $methodName)) {
                throw new HTTPException('Not found', 404);
            }

            $this->pendingAction = [
                'controller' => $controllerName,
                'method' => $methodName,
                'arguments' => $arguments,
            ];

            return true;
        }

        return false;
    }

    /**
     * Matches the URI.
     *
     * @param string $regex
     * @param string $uri
     * @return array|null
     */
    private function matchUri(string $regex, string $uri): ?array
    {
        if (preg_match_all('/' . $regex . '/', $uri, $uriMatches)) {
            $uriMatches = array_filter($uriMatches, function ($key) {
                if (is_string($key)) {
                    return true;
                }

                return false;
            }, ARRAY_FILTER_USE_KEY);

            $uriMatches = array_map(function ($match) {
                return $match[0];
            }, $uriMatches);

            return $uriMatches;
        }

        return null;
    }

    /**
     * Get the arguments for the requested URI.
     *
     * @param string $uri
     * @param string $routeUri
     * @return array|null
     */
    private function getArguments(string $uri, string $routeUri): ?array
    {
        if (preg_match_all('/({[a-z]+:)/i', $routeUri, $matches)) {
            $regex = str_replace('/', '\/',
                str_replace('}', ')',
                    preg_replace('/{([a-z]+):/i', '(?<$1>', $routeUri)
                )
            );

            return $this->matchUri($regex, $uri);
        }

        return null;
    }
}
