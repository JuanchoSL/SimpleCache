# SimpleCache

## Description
A small collection of read/write functions for multiples cache systems

## Install
```
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
### For create a cache instance
```
$cache = new JuanchoSL\SimpleCache\Repositories\ProcessCache($_ENV['CACHE_ENVIRONMENT']);
```
### For write a cache index
The max time to expire is 30 days if you not set a time
```
token = $cache->set(string $cache_key, mixed $value, int $ttl = 0);
```
### For read a cache index
```
$cache_value = $cache->get(string $cache_key);
```
### For delete a cache index
```
$result = $cache->delete(string $cache_key);
```
### For replace a cache index
```
$result = $cache->replace(string $cache_key, mixed $new_value);
```
### For change the time to live of cache index
```
$result = $cache->touch(string $cache_key, int $new_ttl);
```
### For increment a cache index numeric value
```
$result = $cache->increment(string $cache_key, int $numeric_increment, int $stating_value_if_not_exists = 0, int $ttl_if_not_exists = $max_ttl);
```
### For decrement a cache index numeric value
```
$result = $cache->decrement(string $cache_key, int $numeric_decrement, int $stating_value_if_not_exists = 0, int $ttl_if_not_exists = $max_ttl);
```
