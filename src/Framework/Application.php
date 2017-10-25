<?php
/**
 * Application.php.
 *
 * @author Edwin Dayot
 */

namespace Shorty\Framework;

use DI\ContainerBuilder;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\ServerRequest;
use Shorty\Framework\Exceptions\FileNotFoundException;
use Shorty\Framework\Exceptions\Handler;
use Shorty\Framework\Middlewares\RequestHandler;
use Shorty\Framework\Routing\Router;
use Symfony\Component\Dotenv\Dotenv;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
use function Http\Response\send;

/**
 * Class Application.
 */
class Application
{
    /**
     * Instance of application.
     *
     * @var \Shorty\Framework\Application
     */
    private static $instance;

    /**
     * Custom config.
     *
     * @var mixed
     */
    private $config;

    /**
     * Environment object.
     *
     * @var Dotenv
     */
    private $env;

    /**
     * Request object.
     *
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    private $request;

    /**
     * Error Handler object.
     *
     * @var \Whoops\Run
     */
    private $errorHandler;

    /**
     * Response object.
     *
     * @var \Psr\Http\Message\ResponseInterface
     */
    private $response;

    /**
     * Container object.
     *
     * @var \DI\Container
     */
    private $container;

    /**
     * Router object.
     *
     * @var \Shorty\Framework\Routing\Router
     */
    private $router;

    /**
     * Providers array.
     *
     * @var \Shorty\Framework\Provider\Contracts\ServiceProviderInterface[]
     */
    private $providers = [];

    /**
     * Middlewares array.
     *
     * @var \Shorty\Framework\Middlewares\Contracts\MiddlewareInterface[]
     */
    private $middlewares = [];

    /**
     * Route Middlewares array.
     *
     * @var array
     */
    private $routeMiddlewares = [];

    /**
     * Application constructor.
     */
    public function __construct()
    {
        $this->config = config('app');
        $this->createEnv();
        $this->createContainer();
        $this->createRequest();
        $this->registerErrorHandler();
        $this->createRouter();
        $this->registerProviders();
        self::$instance = $this;
    }

    /**
     * Getter for instance.
     *
     * @return \Shorty\Framework\Application
     */
    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Run application.
     */
    public function run(): void
    {
        try {
            $this->handleRequest($this->middlewares);
            $this->router->build();
            $this->handleRequest($this->routeMiddlewares);
            $this->router->run();
        } catch (\Exception $exception) {
            $response = $this->container->call([$this->getExceptionHandler(), 'render'], [$exception]);
            $this->response = $response ?: $this->response;
        }
        send($this->response);
    }

    /**
     * Getter for router.
     *
     * @return \Shorty\Framework\Routing\Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * Add a route middleware to the list of executed middlewares.
     *
     * @param string $name
     */
    public function addRouteMiddleware(string $name): void
    {
        if (!array_key_exists($name, $this->getConfigRouteMiddlewares())) {
            throw new \InvalidArgumentException('The middleware [' . $name . '] does not exists.');
        }

        $this->routeMiddlewares[] = $this->getConfigRouteMiddlewares()[$name];
    }

    /**
     * Create environment.
     */
    private function createEnv(): void
    {
        if (file_exists(base_dir('../.env'))) {
            $dotEnv = new Dotenv();
            $dotEnv->load(base_dir('../.env'));
            $this->env = $dotEnv;
        }
    }

    /**
     * Create the request object.
     */
    private function createRequest(): void
    {
        if (!$this->request) {
            $request = ServerRequest::fromGlobals()
                ->withParsedBody(json_decode(file_get_contents('php://input')));
            $this->container->set(Request::class, $request);
            $this->request = $request;
        }
    }

    private function createRouter(): void
    {
        if (!$this->router) {
            $router = new Router($this->request, $this->container);
            $this->container->set(Router::class, $router);
            $this->router = $router;
        }
    }

    /**
     * Create the response object.
     */
    private function createContainer(): void
    {
        if (!$this->container) {
            $builder = new ContainerBuilder();
            $builder->addDefinitions([
                self::class => $this,
            ]);
            $this->container = $builder->build();
        }
    }

    /**
     * Register the error handler.
     */
    private function registerErrorHandler(): void
    {
        $whoops = new Run();
        $whoops->pushHandler(new PrettyPageHandler());
        $whoops->register();

        $this->errorHandler = $whoops;
    }

    /**
     * Registers the providers by booting them.
     */
    private function registerProviders(): void
    {
        $this->providers = array_merge($this->providers, $this->getConfigProviders());

        foreach ($this->providers as $provider) {
            $provider = $this->container->get($provider);
            $provider->boot();
        }
    }

    /**
     * Handles the request and triggers middlewares.
     *
     * @param array $middlewares
     */
    private function handleRequest(array $middlewares): void
    {
        $this->setBaseMiddlewares();

        $requestHandler = new RequestHandler($this->container, $middlewares);

        $this->response = $requestHandler->handle($this->request);
    }

    /**
     * Get the providers of the config.
     *
     * @return array
     */
    private function getConfigProviders(): array
    {
        if (array_key_exists('providers', $this->config)) {
            return $this->config['providers'];
        }

        return [];
    }

    /**
     * Getter for config's middlewares.
     *
     * @return \Shorty\Framework\Middlewares\Contracts\MiddlewareInterface[]
     */
    private function getConfigMiddlewares(): array
    {
        if (array_key_exists('middlewares', $this->config)) {
            return $this->config['middlewares'];
        }

        return [];
    }

    /**
     * Getter for config's middlewares.
     *
     * @return \Shorty\Framework\Middlewares\Contracts\MiddlewareInterface[]
     */
    private function getConfigBaseMiddlewares(): array
    {
        if (array_key_exists('base', $this->getConfigMiddlewares())) {
            return $this->getConfigMiddlewares()['base'];
        }

        return [];
    }

    /**
     * Getter for config's middlewares.
     *
     * @return \Shorty\Framework\Middlewares\Contracts\MiddlewareInterface[]
     */
    private function getConfigRouteMiddlewares(): array
    {
        if (array_key_exists('route', $this->getConfigMiddlewares())) {
            return $this->getConfigMiddlewares()['route'];
        }

        return [];
    }

    /**
     * Set the base middlewares.
     */
    private function setBaseMiddlewares(): void
    {
        $this->middlewares = array_merge($this->middlewares, $this->getConfigBaseMiddlewares());
    }

    /**
     * Get the exception handler.
     *
     * @return string
     */
    private function getExceptionHandler(): string
    {
        if (!class_exists(\App\Exceptions\Handler::class)) {
            return Handler::class;
        }

        return \App\Exceptions\Handler::class;
    }
}
