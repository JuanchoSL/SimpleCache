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
- Memcached: If Memcached service is available
- Memcache: If Memcached service is available
- Redis: If Redis service is available
- File: The most compatible system, file into filesystem, but slower

## How use it

### Use directly one of the available libs

#### For create a cache instance
```php
use JuanchoSL\SimpleCache\Repositories\ProcessCache;

$cache = new ProcessCache($_ENV['CACHE_ENVIRONMENT']);
```
#### For write a cache index
The max time to expire is 30 days if you do not set a time
```php
$result = $cache->set(string $cache_key, mixed $value, int $ttl = 0);
```
#### For read a cache index
```php
$cache_value = $cache->get(string $cache_key);
```
#### For delete a cache index
```php
$result = $cache->delete(string $cache_key);
```
#### For replace a cache index
```php
$result = $cache->replace(string $cache_key, mixed $new_value);
```
#### For change the time to live of cache index
```php
$result = $cache->touch(string $cache_key, int $new_ttl);
```
#### For increment a cache index numeric value
```php
$result = $cache->increment(string $cache_key, int $numeric_increment, int $stating_value_if_not_exists = 0, int $ttl_if_not_exists = $max_ttl);
```
#### For decrement a cache index numeric value
```php
$result = $cache->decrement(string $cache_key, int $numeric_decrement, int $stating_value_if_not_exists = 0, int $ttl_if_not_exists = $max_ttl);
```

### Use the provided adapter for use with compatibility with PSR-16 Simple-Cache

#### Create a cache instance
```php
use JuanchoSL\SimpleCache\Repositories\ProcessCache;
use JuanchoSL\SimpleCache\Adapters\PsrSimpleCacheAdapter;

$lib = new ProcessCache($_ENV['CACHE_ENVIRONMENT']);
$cache = new PsrSimpleCacheAdapter($lib);
```
#### write a cache index
The max time to expire is 30 days if you do not set a time
```php
$result = $cache->set(string $cache_key, mixed $value, int $ttl = 0);
```
#### check availability for a cache index
```php
$result = $cache->has(string $cache_key);
```
#### read a cache index
```php
$cache_value = $cache->get(string $cache_key);
```
#### delete a cache index
```php
$result = $cache->delete(string $cache_key);
```