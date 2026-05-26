<?php

namespace Statical\SlimStatic\Route;

use IteratorAggregate;
use Slim\App;
use Statical\SlimStatic\View;

class Router
{
    /**
     * Pending middleware to be applied to the next registered route or group.
     * Mimics Laravel's Route::middleware(...)->get(...) chaining.
     */
    private array $pendingMiddleware = [];

    /**
     * Pending controller prefix.
     * Mimics Laravel's Route::controller(MyController::class)->group(...).
     */
    private ?string $pendingController = null;

    private ?string $pendingPrefix = null;

    public function __construct(
        public App|\Slim\Routing\RouteCollectorProxy $slim
    ) {}

    /**
     * Requires that View facade service definition ('view') returns a 
     * renderer with a render() method, i.e. Slim PhpRenderer.
     */
    public function view(
        string $pattern,
        string $template,
        IteratorAggregate|array $data = [],
        int $status = 200,
        array $headers = []
    ): RouteDecorator {

        if ( $data instanceof IteratorAggregate) {
            $data = iterator_to_array($data);
        }

        return $this->get($pattern, function ($request, $response) use ($template, $data, $status, $headers) {
            foreach ($headers as $name => $value) {
                $response = $response->withHeader($name, $value);
            }

            return View::render(
                $response->withStatus($status),
                $template,
                $data
            );
        });
    }

    ############################################################################
    # HTTP VERB METHODS
    ############################################################################

    public function get(string $pattern, callable|array|string $callable): RouteDecorator
    {
        return $this->addRoute(['GET'], $pattern, $callable);
    }

    public function post(string $pattern, callable|array|string $callable): RouteDecorator
    {
        return $this->addRoute(['POST'], $pattern, $callable);
    }

    public function put(string $pattern, callable|array|string $callable): RouteDecorator
    {
        return $this->addRoute(['PUT'], $pattern, $callable);
    }

    public function patch(string $pattern, callable|array|string $callable): RouteDecorator
    {
        return $this->addRoute(['PATCH'], $pattern, $callable);
    }

    public function delete(string $pattern, callable|array|string $callable): RouteDecorator
    {
        return $this->addRoute(['DELETE'], $pattern, $callable);
    }

    public function options(string $pattern, callable|array|string $callable): RouteDecorator
    {
        return $this->addRoute(['OPTIONS'], $pattern, $callable);
    }

    public function any(string $pattern, callable|array|string $callable): RouteDecorator
    {
        return $this->addRoute(['GET','POST','PUT','PATCH','DELETE','OPTIONS'], $pattern, $callable);
    }

    public function match(array $methods, string $pattern, callable|array|string $callable): RouteDecorator
    {
        return $this->addRoute(array_map('strtoupper', $methods), $pattern, $callable);
    }

    ############################################################################
    # CHAINABLE PREFIX METHODS — return $this so they can be chained
    ############################################################################

    /**
     * Route::middleware('auth', 'throttle')->get('/dashboard', ...)
     */
    public function middleware(string ...$middleware): static
    {
        $this->pendingMiddleware = array_merge($this->pendingMiddleware, $middleware);

        return $this;
    }

    /**
     * Within a controller() group, you only need to pass the method name as
     * the callable: 'index' becomes 'UserController:index' (Slim's Class:method syntax).
     * 
     * Route::controller(UserController::class)->group(function () {
     *     Route::get('/users', 'index');
     *     Route::post('/users', 'store');
     * });
     */
    public function controller(string $controller): static
    {
        $this->pendingController = $controller;

        return $this;
    }

    /**
     * Route::prefix('/admin')->group(function () { ... })
     *
     * Note: In Slim 4, the prefix is just the first arg to group(), so this
     * is sugar that stores it and applies it in group().
     */
    public function prefix(string $prefix): static
    {
        # Store it so group() can prepend it to its own pattern.
        # We piggyback on pendingMiddleware's flush pattern; a separate
        # property keeps it explicit.
        $this->pendingPrefix = $prefix;

        return $this;
    }

    /**
     * Route::group('/admin', function () { ... })
     *   or
     * Route::middleware('auth')->prefix('/admin')->group(function () { ... })
     */
    public function group(callable|string $patternOrCallable, ?callable $callable = null ): RouteGroupDecorator
    {
        if (is_callable($patternOrCallable)) {
            $callable = $patternOrCallable;
            $pattern  = '';
        } else {
            $pattern  = $patternOrCallable;
        }
        
        # Apply any pending prefix
        if (isset($this->pendingPrefix)) {
            $pattern = '/'.trim($this->pendingPrefix, '/');
            if ($pattern !== '/') {
                $pattern = rtrim($pattern, '/');
            }
            $this->pendingPrefix = null;
        }

        # Wrap the callable to inject a pending controller into scope if set
        $controller = $this->pendingController;
        $this->pendingController = null;

        $router = $this;

        $wrappedCallable = function (\Slim\Routing\RouteCollectorProxy $group) use ($callable, $controller, $router) {
            # Swap the slim app for the group proxy so inner Route:: calls
            # register against the group, not the root app
            $previousSlim = $router->slim;
            $router->slim = $group;

            if ($controller !== null) {
                $router->pendingController = $controller;
            }

            $callable();

            $router->pendingController = null;
            $router->slim = $previousSlim;
        };

        $group = $this->slim->group($pattern, $wrappedCallable);

        # Apply pending middleware to the group
        foreach (array_reverse($this->flushMiddleware()) as $mw) {
            $group->add($mw);
        }

        return new RouteGroupDecorator($group);
    }

    /**
     * Redirect shorthand — mirrors Laravel's Route::redirect().
     */
    public function redirect(string $from, string $to, int $status = 302): RouteDecorator
    {
        return $this->get($from, function ($request, $response) use ($to, $status) {
            return $response->withHeader('Location', $to)->withStatus($status);
        });
    }

    ############################################################################
    # PRIVATE HELPERS
    ############################################################################

    private function addRoute(array $methods, string $pattern, callable|array|string $callable): RouteDecorator
    {
        # Resolve "MethodName" shorthand when a controller is pending
        if ($this->pendingController !== null && is_string($callable) && !str_contains($callable, ':')) {
            $callable = $this->pendingController.':'.$callable;
            # Don't clear pendingController — it stays for the whole group scope
        }

        # Only normalize when inside a group proxy — root '/' must stay as-is
        if ($this->slim instanceof \Slim\Routing\RouteCollectorProxy 
            && !$this->slim instanceof \Slim\App
        ) {
            $pattern = '/'.trim($pattern, '/');
            if ($pattern === '/') {
                $pattern = '';
            }
        }

        $route = $this->slim->map($methods, $pattern, $callable);

        # Apply and flush any pending middleware (last-registered = outermost)
        foreach (array_reverse($this->flushMiddleware()) as $mw) {
            $route->add($mw);
        }

        return new RouteDecorator($route);
    }

    private function flushMiddleware(): array
    {
        $mw = $this->pendingMiddleware;
        $this->pendingMiddleware = [];
        return $mw;
    }

    private function wrapWithController(callable $callable, string $controller): callable
    {
        # Make the controller string available inside the route file's scope
        # by passing it through a closure that sets pendingController before
        # each verb call so individual routes inherit it.
        $router = $this;

        return function () use ($callable, $controller, $router) {
            $router->pendingController = $controller;
            $callable();
            $router->pendingController = null;
        };
    }
}