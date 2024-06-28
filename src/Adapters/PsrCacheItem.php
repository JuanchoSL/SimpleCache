<?php

declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Adapters;

use Psr\Cache\CacheItemInterface;

class PsrCacheItem implements CacheItemInterface
{
    private string $key;
    private mixed $value = null;
    private ?int $expirationTimestamp;
    private bool $hit = false;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function getExpirationTimestamp(): ?int
    {
        return $this->expirationTimestamp;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get(): mixed
    {
        return $this->value;
    }

    public function isHit(): bool
    {
        return $this->hit;
    }

    public function set(mixed $value): static
    {
        $this->value = $value;
        $this->hit = true;
        return $this;
    }


    public function expiresAt(?\DateTimeInterface $expiration): static
    {
        if ($expiration === null) {
            $this->expirationTimestamp = null;
        }elseif ($expiration instanceof \DateTimeInterface) {
            $this->expirationTimestamp = $expiration->getTimestamp();
        } elseif (is_int($expiration)) {
            $this->expirationTimestamp = $expiration;
        }
        return $this;
    }

    public function expiresAfter(int|\DateInterval|null $time): static
    {
        if ($time === null) {
            $this->expirationTimestamp = null;
        } elseif ($time instanceof \DateInterval) {
            $date = new \DateTime();
            $date->add($time);
            $this->expirationTimestamp = $date->getTimestamp();
        } elseif (is_int($time)) {
            $this->expirationTimestamp = time() + $time;
        }
        return $this;
    }
}