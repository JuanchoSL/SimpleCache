<?php

declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Repositories;

trait CommonTrait
{

    protected function maxTtl(?int $ttl): int
    {
        if (empty($ttl)) {
            $ttl = 3600 * 24 * 30;
        }
        return $ttl;
    }
}