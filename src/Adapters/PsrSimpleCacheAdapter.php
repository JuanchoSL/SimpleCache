<?php declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Adapters;

use JuanchoSL\Exceptions\PreconditionFailedException;
use JuanchoSL\Validators\Types\Strings\StringValidations;
use Psr\SimpleCache\CacheInterface;

class PsrSimpleCacheAdapter implements CacheInterface
{
    private CacheInterface $cache;

    protected string $chars = 'a-zA-Z0-9_.';
    protected int $max_lenght = 64;
    protected string $extra_chars = '';

    public static function getInstance(CacheInterface $cache): CacheInterface
    {
        return new self($cache);
    }

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function getPattern(): string
    {
        return "/^[{$this->chars}{$this->extra_chars}]{1,{$this->max_lenght}}+\$/";//$this->pattern;
    }

    public function setExtraChars(string $extra_chars): void
    {
        $this->extra_chars = $extra_chars;
    }
    public function setMaxKeyLenght(int $max_lenght): void
    {
        if ($max_lenght < 64) {
            throw new PreconditionFailedException("The max lenght needs to be 64 or bigger");
        }
        $this->max_lenght = $max_lenght;
    }

    public function has(string $key): bool
    {
        $this->checkKey($key);
        return $this->cache->has($key);
    }

    public function clear(): bool
    {
        return $this->cache->clear();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->checkKey($key);
        return $this->cache->get($key, $default);
    }

    /**
     * @param iterable<int, string> $keys
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $this->checkKeys($keys);
        return $this->cache->getMultiple($keys, $default);
    }

    public function set(string $key, mixed $value, \DateInterval|null|int $ttl = null): bool
    {
        $this->checkKey($key);
        return $this->cache->set($key, $value, $ttl);
    }

    /**
     * @param iterable<string, mixed> $values
     */
    public function setMultiple(iterable $values, \DateInterval|null|int $ttl = null): bool
    {
        $this->checkKeys(array_keys($values));
        return $this->cache->setMultiple($values, $ttl);
    }

    public function delete(string $key): bool
    {
        $this->checkKey($key);
        return $this->cache->delete($key);
    }

    /**
     * @param iterable<int, string> $keys
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $this->checkKeys($keys);
        return $this->cache->deleteMultiple($keys);
    }

    protected function checkKeys(iterable $keys)
    {
        foreach ($keys as $key) {
            $this->checkKey($key);
        }
    }
    protected function checkKey(string $key)
    {
        if (!(new StringValidations)->isNotEmpty()->isRegex($this->getPattern())->getResult($key)) {
            throw new \InvalidArgumentException("The key '{$key}' is not valid");
        }
        return true;
    }
}