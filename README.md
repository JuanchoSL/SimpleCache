# SimpleCache

## Description

A small collection of read/write functions for multiples cache systems

## Install

```bash
composer require juanchosl/simplecache
```

## Performance

From faster to slower (miliseconds), with a set and get data test, counting the time in miliseconds

| set 10 elements  | get 10 elements  |
|:-----------------|:-----------------|
| <ol><li>Session: 0.0911</li><li>Apcu: 0.1049</li><li>Yac: 0.3328</li><li>File: 6.731</li><li>Redis: 7.4098</li><li>Memcached: 8.9169</li><li>Process: 9.8691</li><li>Memcache: 25.527</li></ol> | <ol><li>Apcu: 0.0889</li><li>Session: 0.0958</li><li>Yac: 0.1299</li><li>Process: 0.2308</li><li>File: 4.1699</li><li>Memcached: 6.541</li><li>Memcache: 11.636</li><li>Redis: 20.7901</li></ol> |

| set 100 elements | get 100 elements |
| ------------------- | ------------------- |
| <ol><li>Session: 0.932</li><li>Process: 0.962</li><li>Apcu: 0.9041</li><li>Yac: 1.009</li><li>Redis: 69.5832</li><li>File: 71.667</li><li>Memcache: 165.0958</li><li>Memcached: 601.537</li></ol> | <ol><li>Process: 0.947</li><li>Session: 0.8419</li><li>Yac: 0.8719</li><li>Apcu: 1.6818</li><li>File: 18.5752</li><li>Memcache: 82.8669</li><li>Memcached: 97.2202</li><li>Redis: 134.5811</li></ol> |

| set 1000 elements | get 1000 elements |
|------------------|------------------|
| <ol><li>Yac: 9.86</li><li>Session: 9.871</li><li>Apcu: 10.2232</li><li>Process: 10.4311</li><li>File: 593.3461</li><li>Memcached: 857.6429</li><li>Redis: 1663.4059</li><li>Memcache: 3402.5531</li></ol> | <ol><li>Session: 8.606</li><li>Process: 9.0408</li><li>Apcu: 9.0988</li><li>Yac: 9.692</li><li>File: 204.6311</li><li>Memcached: 824.0871</li><li>Memcache: 1020.0572</li><li>Redis: 2280.118</li></ol> |

- Process: It's only valid for current request execution
- Session: Only valid for a current user session
- APCU: Cache into user scope, saved on memory for fast access. APCU Module is required
- YAC: Cache into user scope, saved on memory for fast access. Alternative for APCU, YAC Module is required
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

Read from cache the contents of `$cache_keys` and return a list of `$key => $value` pairs. Missed keys will be filled with the `$default` value if it is provided and distinct of NULL, or ignored otherwise.

> default values can be:
>
> - a value for all (distinct of null)
> - a list with the same array indexes of the required keys and an exclusive default value or each key
> - a collection of keys => default_value (primitive, class with an \_\_invoke method or closure)

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
