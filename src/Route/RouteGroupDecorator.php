<?php

namespace Statical\SlimStatic\Route;

use Slim\Interfaces\RouteGroupInterface;

class RouteGroupDecorator
{
    public function __construct(private readonly RouteGroupInterface $group) {}

    public function add(mixed $middleware): static
    {
        $this->group->add($middleware);
        return $this;
    }

    public function middleware(string ...$middleware): static
    {
        foreach (array_reverse($middleware) as $mw) {
            $this->group->add($mw);
        }
        return $this;
    }

    public function __call(string $name, array $args): mixed
    {
        $result = $this->group->{$name}(...$args);
        return $result === $this->group ? $this : $result;
    }
}