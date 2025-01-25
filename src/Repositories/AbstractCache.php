<?php declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Repositories;

use JuanchoSL\SimpleCache\Contracts\SimpleCacheInterface;
use Psr\Log\LoggerAwareTrait;

abstract class AbstractCache implements SimpleCacheInterface
{

    use CommonTrait, LoggerAwareTrait;

    protected bool $debug = false;

    public function setDebug(bool $debug = false): static
    {
        $this->debug = $debug;
        return $this;
    }

    protected function log(\Stringable|string $message, $log_level, $context = [])
    {
        if (isset($this->logger)) {
            if ($this->debug || $log_level != 'debug') {
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
        foreach ($keys as $key) {
            $response[$key] = $this->get($key) ?? $default;
        }
        return $response;
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