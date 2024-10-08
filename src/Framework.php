<?php

namespace FilippoToso\Microvel;

use FilippoToso\Microvel\Support\Path;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\Router as Routing;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Arr;
use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Capsule\Manager as DatabaseManager;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Facades\Facade;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory as ViewFactory;
use Illuminate\View\FileViewFinder;
use Illuminate\Pagination\Paginator;
use Illuminate\Session\SessionManager;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Mail\MailManager;
use Throwable;

class Framework
{
    protected static $self;

    protected App $container;
    protected Request $request;
    protected Dispatcher $events;
    protected Routing $router;
    protected Redirector $redirector;
    protected DatabaseManager $database;
    protected array $config;
    protected FilesystemManager $storage;
    protected ViewFactory $view;
    protected BladeCompiler $blade;
    protected SessionManager $session;
    protected UrlGenerator $url;

    public function __construct($config)
    {
        static::$self = $this;

        $this->config = $config;

        $this->container = App::getInstance();

        $this->request = Request::capture();

        $this->container->instance('Illuminate\Http\Request', $this->request);

        $this->events = new Dispatcher($this->container);

        $this->router = new Routing($this->events, $this->container);

        // Will be replaced in static::routes()
        //$this->url = new UrlGenerator($this->router->getRoutes(), $this->request);

        $this->database = new DatabaseManager;
        $this->database->setEventDispatcher($this->events);
        $this->database->setAsGlobal();
        $this->database->bootEloquent();
        $this->database->addConnection($this->config('database'), 'default');

        date_default_timezone_set($this->config('app.timezone', 'UTC'));

        $this->container->instance('app', $this->container);
        $this->container['config'] = new Config($this->config);

        $this->storage = (new FilesystemManager($this->container));
        $this->container->instance(FilesystemFactory::class, $this->storage);

        $this->container->instance(Application::class, $this->container);

        $pathsToTemplates = [Path::resources('views')];
        $pathToCompiledTemplates = Path::storage('framework/views');

        $filesystem = new Filesystem;

        $viewResolver = new EngineResolver;
        $this->blade = new BladeCompiler($filesystem, $pathToCompiledTemplates);

        $viewResolver->register('blade', function () {
            return new CompilerEngine($this->blade);
        });

        $viewFinder = new FileViewFinder($filesystem, $pathsToTemplates);
        $this->view = new ViewFactory($viewResolver, $viewFinder, $this->events);
        $this->view->setContainer($this->container);

        $this->view->share('errors', new ViewErrorBag);

        Facade::setFacadeApplication($this->container);

        $this->container->instance(\Illuminate\Contracts\View\Factory::class, $this->view);
        $this->container->alias(
            \Illuminate\Contracts\View\Factory::class,
            (new class extends \Illuminate\Support\Facades\View
            {
                public static function getFacadeAccessor()
                {
                    return parent::getFacadeAccessor();
                }
            })::getFacadeAccessor()
        );

        $this->container->instance(\Illuminate\View\Compilers\BladeCompiler::class, $this->blade);
        $this->container->alias(
            \Illuminate\View\Compilers\BladeCompiler::class,
            (new class extends \Illuminate\Support\Facades\Blade
            {
                public static function getFacadeAccessor()
                {
                    return parent::getFacadeAccessor();
                }
            })::getFacadeAccessor()
        );

        Paginator::viewFactoryResolver(function () {
            return $this->view;
        });

        Paginator::currentPathResolver(function () {
            return strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
        });

        Paginator::currentPageResolver(function ($pageName = 'page') {
            return $_REQUEST[$pageName] ?? 1;
        });

        // Setup session
        $this->container['files'] = new Filesystem;

        if ($this->config['session'] ?? false) {

            $this->session = new SessionManager($this->container);
            $this->container['session.store'] = $this->session->driver();
            $this->container['session'] = $this->session;

            // In order to maintain the session between requests, we need to populate the
            // session ID from the supplied cookie
            $cookieName = $this->container['session']->getName();

            if (isset($_COOKIE[$cookieName])) {
                if ($sessionId = $_COOKIE[$cookieName]) {
                    $this->container['session']->setId($sessionId);
                }
            }

            $this->container['session']->start();
        }

        $this->container->singleton('mail.manager', function ($app) {
            return new MailManager($app);
        });

        $this->container->bind('mailer', function ($app) {
            return $app->make('mail.manager')->mailer();
        });

        $this->container->singleton('events', function ($app) {
            return (new Dispatcher($app));
        });
    }

    public static function instance()
    {
        return static::$self;
    }

    public function routes(string $routesFile)
    {
        include($routesFile);

        $this->router->getRoutes()->refreshNameLookups();

        $this->url = new UrlGenerator($this->router->getRoutes(), $this->request);

        $this->redirector = new Redirector($this->url);
    }

    public function process()
    {
        try {
            $response = $this->router->dispatch($this->request);
        } catch (Throwable $th) {
            $response = $this->container->make(\Illuminate\Contracts\Debug\ExceptionHandler::class)
                ->render($this->request, $th);
        }

        $response->send();
    }

    /**
     * @param string $disk
     * @return Filesystem
     */
    public function storage($disk = 'local')
    {
        return $this->storage->disk($disk);
    }

    /**
     * @return ViewFactory
     */
    public function view()
    {
        return $this->view;
    }

    /**
     * @return BladeCompiler
     */
    public function blade()
    {
        return $this->blade;
    }

    public function config($key, $default = null)
    {
        return Arr::get($this->config, $key, $default);
    }

    /**
     * @param string|null $connection
     * @return Connection
     */
    public function database($connection = null)
    {
        $connection = $connection ?? $this->config('database.default') ?? 'default';
        return $this->database->getConnection($connection);
    }

    /**
     * @return Routing
     */
    public function router()
    {
        return $this->router;
    }

    /**
     * @return Request
     */
    public function request()
    {
        return $this->request;
    }

    /**
     * @return SessionManager
     */
    public function session()
    {
        return $this->container['session'];
    }

    /**
     * @return UrlGenerator
     */
    public function url()
    {
        return $this->url;
    }
}
