<?php

declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Repositories;

trait CommonTrait
{
    protected int $maxttl = 3600 * 24 * 30;

    public function setMaxTtl(\DateInterval|int $ttl)
    {
        if (empty($ttl)) {
            $ttl = 0;
        } elseif ($ttl instanceof \DateInterval) {
            $ttl = (int) $ttl->format("%s");
        }
        $this->maxTtl = $ttl;
    }
    protected function maxTtl(\DateInterval|null|int $ttl = null): int
    {
        if (empty($ttl)) {
            $ttl = $this->maxttl;
        } elseif ($ttl instanceof \DateInterval) {
            $ttl = (int) $ttl->format("%s");
        }
        return $ttl;
    }
}