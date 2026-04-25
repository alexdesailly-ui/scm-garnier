<?php

declare(strict_types=1);

namespace SCM\Core;

final class Config
{
    private array $values;

    public function __construct(array $values = [])
    {
        $this->values = $values;
    }

    public static function fromEnvFile(string $path): self
    {
        if (!file_exists($path)) {
            return new self();
        }

        $values = require $path;

        return new self(is_array($values) ? $values : []);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->values)) {
            return $this->values[$key];
        }

        $env = getenv($key);

        return $env !== false ? $env : $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->values[$key] = $value;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->values) || getenv($key) !== false;
    }

    public function all(): array
    {
        return $this->values;
    }

    public function merge(array $values): self
    {
        return new self(array_merge($this->values, $values));
    }
}
