# SimpleCache

## Description

A small collection of read/write functions for multiples cache systems

## Install

```bash
composer require juanchosl/simplecache
```

## Performance

From faster to slower

- Process: It's only valid for current request execution
- Session: Only valid for a current user session
- Memcached: If Memcached service is available and Memcached library is installed
- Redis: If Redis service is available and Redis library is installed
- Memcache: If Memcached service is available and Memcache library is installed
- File: The most compatible system, file into filesystem, but slower

## How use it

### Use directly one of the available libs

#### For create a cache instance

```php
use JuanchoSL\SimpleCache\Repositories\ProcessCache;

$cache = new ProcessCache($_ENV['CACHE_ENVIRONMENT']);
//The max time to expire is 30 days if you do not set a time
$cache->setMaxTtl(3600 * 24);
```

#### For write a cache index

Set into `$cache_key` the `$value`, valid for `$ttl` seconds or default TTL if you don not pass a value

```php
$result = $cache->set(string $cache_key, mixed $value, int $ttl = 0);
```

#### For read a cache index

Read from cache the contents of `$cache_key` and return his value or `$default` if it not exists or it is not valid

```php
$cache_value = $cache->get(string $cache_key, $default = null);
```

#### For delete a cache index

Delete from cache the value with `$cache_key`

```php
$result = $cache->delete(string $cache_key);
```

#### For write multiple cache indexes

Set into `$cache_key` the `$values`, an iterable containing a list of `$cache_key => $value` pairs, valid for `$ttl` seconds or default TTL if you don not pass a value

```php
$result = $cache->setMultiple(iterable $values, \DateInterval|int $ttl = 0);
```

#### For read multiple cache indexes

Read from cache the contents of `$cache_keys` and return a list of `$key => $value` pairs. Missed keys has the `$default` value

```php
$cache_value = $cache->getMultiple(iterable $cache_keys, $default = null);
```

#### For delete a cache index

Delete from cache the values from the `$cache_keys` list

```php
$result = $cache->deleteMultiple(iterable $cache_keys);
```

#### For replace a cache index

Replace into cache the value with `$cache_key` with the `$new_value` without change his expiration time

```php
$result = $cache->replace(string $cache_key, mixed $new_value);
```

#### For change the time to live of cache index

Change the expiration time of `$cache_key` with the new one passed as `$new_ttl`

```php
$result = $cache->touch(string $cache_key, \DateInterval|int $new_ttl);
```

#### For increment a cache index numeric value

Increments the value into `$cache_key` adding `$numeric_increment` to his value. If not exists it is created.

```php
$result = $cache->increment(string $cache_key, int|float $numeric_increment, \DateInterval|int $ttl_if_not_exists = $max_ttl);
```

#### For decrement a cache index numeric value

Decrements the value into `$cache_key` subtracting `$numeric_decrement` to his value. If not exists it is created.

```php
$result = $cache->decrement(string $cache_key, int|float $numeric_decrement, \DateInterval|int $ttl_if_not_exists = $max_ttl);
```

#### For check if the cache contains a `$cache_key`

Check if key exists, is not recommended, because can be return true and just another script can remove it

```php
$result = $cache->has(string $cache_key);
```

#### For clear all cache indexes

Remove all data from cache

```php
$result = $cache->clear();
```

### Use the provided adapter for use with compatibility with PSR-16 Simple-Cache

#### Create a cache instance

After create a Cache Instance, you can use it with the provided PsrSimpleCacheAdapter in order to work conform the PSR-16 https://www.php-fig.org/psr/psr-16/

```php
use JuanchoSL\SimpleCache\Repositories\ProcessCache;
use JuanchoSL\SimpleCache\Adapters\PsrSimpleCacheAdapter;

$lib = new ProcessCache($_ENV['CACHE_ENVIRONMENT']);
$cache = new PsrSimpleCacheAdapter($lib);
```
