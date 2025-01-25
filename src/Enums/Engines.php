<?php declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Enums;

enum Engines
{
    case MEMCACHE;
    case MEMCACHED;
    case REDIS;
    case FILE;
    case PROCESS;
    case SESSION;
}