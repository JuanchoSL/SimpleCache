<?php declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Repositories;

use JuanchoSL\SimpleCache\Contracts\SimpleCacheInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;

abstract class AbstractCache implements SimpleCacheInterface
{

    use CommonTrait, LoggerAwareTrait;

    protected bool $debug = false;

    public function setDebug(bool $debug = false): static
    {
        $this->debug = $debug;
        return $this;
    }

    protected function log(\Stringable|string $message, mixed $log_level, array $context = [])
    {
        if (isset($this->logger)) {
            if ($this->debug || $log_level != LogLevel::DEBUG) {
                if ($this->debug) {
                    $context['memory'] = memory_get_usage();
                } elseif (array_key_exists('data', $context)) {
                    unset($context['data']);
                }
                $context['Engine'] = (new \ReflectionClass($this))->getShortName();
                $this->logger->log($log_level, $message, $context);
            }
        }
    }
    public function has(string $key): bool
    {
        return ($this->get($key) !== null);
    }

    /**
     * @param iterable<string, mixed> $values
     */
    public function setMultiple(iterable $values, \DateInterval|null|int $ttl = null): bool
    {
        $result = true;
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $this->maxTtl($ttl))) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * @param iterable<int, string> $keys
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $response = [];
        $default_value = $default;
        foreach ($keys as $i => $key) {
            if (is_iterable($default)) {
                $default_value = (array_key_exists($key, $default)) ? $default[$key] : $default[$i];
            }
            if (!is_null($value = $this->get($key, $default_value))) {
                $response[$key] = $value;
            }
            //$response[$key] = $this->get($key, $default_value) ?? $default_value;
        }
        return $response;
    }
public function increment(string $key, int|float $increment = 1, \DateInterval|null|int $ttl = null): int|float|bool
    {
        $value = $this->get($key);
        if (!$value) {
            if ($this->set($key, $increment, $ttl)) {
                return $increment;
            }
        } else {
            $new_value = $value + $increment;
            if ($this->replace($key, $new_value))
                return $new_value;
        }
        return false;
    }
    public function decrement(string $key, int|float $decrement = 1, \DateInterval|null|int $ttl = null): int|float|bool
    {
        $value = $this->get($key);
        if (!$value) {
            $decrement *= -1;
            if ($this->set($key, $decrement, $ttl)) {
                return $decrement;
            }
        } else {
            $new_value = $value - $decrement;
            if ($this->replace($key, $new_value)) {
                return $new_value;
            }
        }
        return false;
    }
    /**
     * @param iterable<int, string> $keys
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $result = true;
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $result = false;
            }
        }
        return $result;
    }

    public function flush(): bool
    {
        return $this->clear();
    }
}