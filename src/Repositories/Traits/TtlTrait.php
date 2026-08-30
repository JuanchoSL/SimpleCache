<?php declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Repositories\Traits;

trait TtlTrait
{
    protected int $maxttl = 3600 * 24 * 30;

    public function setMaxTtl(\DateInterval|int $ttl): static
    {
        if (empty($ttl)) {
            $ttl = 0;
        } elseif ($ttl instanceof \DateInterval) {
            $ttl = (int) $ttl->format("%s");
        }
        $this->maxttl = $ttl;
        return $this;
    }
    protected function maxTtl(\DateInterval|null|int $ttl = null): int
    {
        if (is_null($ttl)) {
            $ttl = $this->maxttl;
        } elseif ($ttl instanceof \DateInterval) {
            $ttl = (int) $ttl->format("%s");
        }
        return $ttl;
    }
}