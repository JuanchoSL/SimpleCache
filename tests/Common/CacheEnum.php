<?php

namespace JuanchoSL\SimpleCache\Tests\Common;

enum CacheEnum
{
    case FILE;
    case MEMCACHE;
    case MEMCACHED;
    case PROCESS;
    case REDIS;
    case SESSION;
}