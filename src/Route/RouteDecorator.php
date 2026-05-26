<?php

namespace Statical\SlimStatic\Route;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Interfaces\InvocationStrategyInterface;
use Slim\Interfaces\RouteInterface;

/**
 * This route decorator is required to:
 * -- Use ->name() instead of slim's ->setName() on routes.
 * -- Use ->whereIn() style pattern matching
 */
/**
 * Fluent wrapper around Slim's RouteInterface that adds Laravel-style
 * helper methods. Does NOT implement RouteInterface itself to avoid
 * breaking Slim's internal route registry.
 */
class RouteDecorator
{
    public function __construct(private RouteInterface $route) {}

    # -------------------------------------------------------------------------
    # NAMED ROUTES: name() instead of Slim's setName()
    # -------------------------------------------------------------------------

    public function name(string $name): static
    {
        $this->route->setName($name);
        return $this;
    }

    # -------------------------------------------------------------------------
    # PATTERN MATCHING
    # -------------------------------------------------------------------------

    /**
     * Constrain a parameter to a raw regex pattern.
     * ->where('id', '[0-9]+')
     */
    public function where(string $parameter, string $pattern): static
    {
        $current = $this->route->getPattern();
        $this->route->setPattern(
            str_replace('{' . $parameter . '}', '{' . $parameter . ':' . $pattern . '}', $current)
        );
        return $this;
    }

    /**
     * Constrain a parameter to a list of allowed values.
     * ->whereIn('status', ['active', 'inactive'])  →  {status:active|inactive}
     */
    public function whereIn(string $parameter, array $values): static
    {
        return $this->where(
            $parameter,
            implode('|', array_map('preg_quote', $values, array_fill(0, count($values), '/')))
        );
    }

    /**
     * Constrain a parameter to numeric values only.
     * ->whereNumber('id')
     */
    public function whereNumber(string $parameter): static
    {
        return $this->where($parameter, '[0-9]+');
    }

    /**
     * Constrain a parameter to alpha values only.
     * ->whereAlpha('slug')
     */
    public function whereAlpha(string $parameter): static
    {
        return $this->where($parameter, '[a-zA-Z]+');
    }

    /**
     * Constrain a parameter to alphanumeric + hyphens — typical slug pattern.
     * ->whereSlug('slug')
     */
    public function whereSlug(string $parameter): static
    {
        return $this->where($parameter, '[a-z0-9-]+');
    }

    /**
     * Constrain a parameter to a UUID.
     * ->whereUuid('id')
     */
    public function whereUuid(string $parameter): static
    {
        return $this->where($parameter, '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
    }


    # -------------------------------------------------------------------------
    # PASS THROUGH
    # -------------------------------------------------------------------------

    public function add(mixed $middleware): static
    {
        $this->route->add($middleware);
        return $this;
    }

    public function setName(string $name): static
    {
        $this->route->setName($name);
        return $this;
    }

    public function getRoute(): RouteInterface
    {
        return $this->route;
    }

    public function __call(string $name, array $args): mixed
    {
        return $this->route->{$name}(...$args);
    }
}