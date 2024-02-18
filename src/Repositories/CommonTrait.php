<?php

declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Repositories;

trait CommonTrait
{

    protected function maxTtl(\DateInterval|null|int $ttl = null): int
    {
        if (empty($ttl)) {
            $ttl = 3600 * 24 * 30;
        } elseif ($ttl instanceof \DateInterval) {
            $ttl = (int) $ttl->format("%s");
        }
        return $ttl;
    }
}