<?php declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Repositories\Traits;

trait GenericCountersTrait
{

    public function increment(string $key, int|float $increment = 1, \DateInterval|null|int $ttl = null): int|float|bool
    {
        if (!$this->has($key)) {
            if ($this->set($key, $increment, $ttl)) {
                return $increment;
            }
        } else {
            $new_value = $this->get($key, 0) + $increment;
            if ($this->replace($key, $new_value))
                return $new_value;
        }
        return false;
    }

    public function decrement(string $key, int|float $decrement = 1, \DateInterval|null|int $ttl = null): int|float|bool
    {
        if (!$this->has($key)) {
            $decrement *= -1;
            if ($this->set($key, $decrement, $ttl)) {
                return $decrement;
            }
        } else {
            $new_value = $this->get($key, 0) - $decrement;
            if ($this->replace($key, $new_value)) {
                return $new_value;
            }
        }
        return false;
    }

}