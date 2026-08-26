<?php declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Repositories\Traits;

trait GenericSingleValueTrait
{

    public function has(string $key): bool
    {
        return ($this->get($key) !== null);
    }

    public function touch(string $key, \DateInterval|null|int $ttl): bool
    {
        if ($this->has($key)) {
            return $this->set($key, $this->get($key), $ttl);
        }
        return false;
    }

    public function replace(string $key, mixed $value): bool
    {
        return ($this->has($key)) ? $this->set($key, $value) : false;
    }
}