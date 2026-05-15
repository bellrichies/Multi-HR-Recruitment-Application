<?php

declare(strict_types=1);

namespace App\Core;

use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

class Container
{
    private array $bindings = [];
    private array $instances = [];

    public function bind(string $abstract, callable|string|null $concrete = null): void
    {
        $this->bindings[$abstract] = $concrete ?? $abstract;
    }

    public function singleton(string $abstract, callable|string|null $concrete = null): void
    {
        $this->bindings[$abstract] = $concrete ?? $abstract;
        $this->instances[$abstract] = null;
    }

    public function instance(string $abstract, mixed $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function get(string $abstract): mixed
    {
        if (array_key_exists($abstract, $this->instances) && $this->instances[$abstract] !== null) {
            return $this->instances[$abstract];
        }

        $concrete = $this->bindings[$abstract] ?? $abstract;
        $object = is_callable($concrete) ? $concrete($this) : $this->build($concrete);

        if (array_key_exists($abstract, $this->instances)) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    private function build(string $class): object
    {
        if (! class_exists($class)) {
            throw new RuntimeException("Class {$class} cannot be resolved.");
        }

        $reflection = new ReflectionClass($class);

        if (! $reflection->isInstantiable()) {
            throw new RuntimeException("Class {$class} is not instantiable.");
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return $reflection->newInstance();
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }

            if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
                $dependencies[] = $this->get($type->getName());
                continue;
            }

            throw new RuntimeException("Cannot resolve parameter {$parameter->getName()} for {$class}.");
        }

        return $reflection->newInstanceArgs($dependencies);
    }
}
