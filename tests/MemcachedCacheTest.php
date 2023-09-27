<?php

namespace JuanchoSL\SimpleCache\Tests;

use JuanchoSL\SimpleCache\Contracts\SimpleCacheInterface;
use JuanchoSL\SimpleCache\Repositories\MemCached;
use PHPUnit\Framework\TestCase;
use JuanchoSL\SimpleCache\Adapters\SimpleCacheAdapter;

class MemcachedCacheTest extends TestCase
{
    private $cache;

    private $value_plain = 'value';
    private $value_array = ['value'];

    private $ttl = 10;

    public function setUp(): void
    {
        $this->cache = new SimpleCacheAdapter(new MemCached($_ENV['MEMCACHE_HOST']));
    }
    public function tearDown(): void
    {
        $this->cache->flush();
    }
    public function testLoad()
    {
        $this->assertInstanceOf(SimpleCacheAdapter::class, $this->cache);
       // $this->assertInstanceOf(SimpleCacheInterface::class, $this->cache);
    }
    public function testSet()
    {
        $result = $this->cache->set('key_memcached', $this->value_plain, $this->ttl);
        $this->assertTrue($result);
    }
    public function testGetOk()
    {
        $result = $this->cache->set('key_memcached', $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        $read_ok = $this->cache->get('key_memcached');
        $this->assertEquals($this->value_plain, $read_ok);
    }
    public function testGetKo()
    {
        $result = $this->cache->set('key_memcached', $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        sleep($this->ttl + 1);
        $read_ko = $this->cache->get('key_memcached');
        $this->assertFalse($read_ko);
    }
    public function testTouch()
    {
        $result = $this->cache->set('key_memcached', $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        $read_ok = $this->cache->get('key_memcached');
        $this->assertEquals($this->value_plain, $read_ok);
        sleep(intval($this->ttl / 2));
        $touch = $this->cache->touch('key_memcached', $this->ttl);
        $this->assertTrue($touch);
        sleep(intval($this->ttl / 2) + 1);
        $read_ok = $this->cache->get('key_memcached');
        $this->assertEquals($this->value_plain, $read_ok);
    }
    public function testReplace()
    {
        $result = $this->cache->set('key_memcached', $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        $read_ok = $this->cache->get('key_memcached');
        $this->assertEquals($this->value_plain, $read_ok);
        $replace = $this->cache->replace('key_memcached', $this->value_plain . "-" . $this->value_plain);
        $this->assertTrue($replace);
        $read_ok = $this->cache->get('key_memcached');
        $this->assertEquals($this->value_plain . "-" . $this->value_plain, $read_ok);
    }
    public function testDelete()
    {
        $result = $this->cache->set('key_memcached', $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        $read_ok = $this->cache->get('key_memcached');
        $this->assertEquals($this->value_plain, $read_ok);
        $result = $this->cache->delete('key_memcached');
        $this->assertTrue($result);
        $read_ko = $this->cache->get('key_memcached');
        $this->assertFalse($read_ko);
    }
    /*public function testAllKeys()
    {
        $result = $this->cache->set('key_memcached', $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        $results = $this->cache->getAllKeys();
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $this->assertContains('key_memcached', $results);
    }*/
    public function testSetArray()
    {
        $result = $this->cache->set('array_memcached', ['key_memcached' => 'value'], $this->ttl);
        $this->assertTrue($result);
        $results = $this->cache->get('array_memcached');
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $this->assertArrayHasKey('key_memcached', $results);
        $this->assertEquals('value', $results['key_memcached']);
    }
    public function testSetObject()
    {
        $obj = new \stdClass;
        $obj->key = 'value';
        $result = $this->cache->set('object_memcached', $obj, $this->ttl);
        $this->assertTrue($result);
        $results = $this->cache->get('object_memcached');
        $this->assertIsObject($results);
        $this->assertObjectHasProperty('key', $results);
        $this->assertEquals('value', $results->key);
    }
    public function testIncrement()
    {
        $initial = $this->cache->increment('key_increment_memcached', 1, $this->ttl);
        $this->assertEquals(1, $initial);
        $initial = $this->cache->increment('key_increment_memcached', 1, $this->ttl);
        $this->assertEquals(2, $initial);
        $initial = $this->cache->increment('key_increment_memcached', 2, $this->ttl);
        $this->assertEquals(4, $initial);
    }

    public function testDecrement()
    {
        $initial = $this->cache->decrement('key_decrement_memcached', 1, $this->ttl);
        $this->assertEquals(-1, $initial);
        $initial = $this->cache->decrement('key_decrement_memcached', 1, $this->ttl);
        $this->assertEquals(-2, $initial);
        $initial = $this->cache->decrement('key_decrement_memcached', 2, $this->ttl);
        $this->assertEquals(-4, $initial);
    }
    public function testIncrementFloat()
    {
        $initial = $this->cache->increment('key_increment_memcached_float', 1.5, $this->ttl);
        $this->assertEquals(1.5, $initial);
        $initial = $this->cache->increment('key_increment_memcached_float', 1.5, $this->ttl);
        $this->assertEquals(3, $initial);
        $initial = $this->cache->increment('key_increment_memcached_float', 2, $this->ttl);
        $this->assertEquals(5, $initial);
    }

    public function testDecrementFloat()
    {
        $initial = $this->cache->decrement('key_decrement_memcached_float', 1.5, $this->ttl);
        $this->assertEquals(-1.5, $initial);
        $initial = $this->cache->decrement('key_decrement_memcached_float', 1.5, $this->ttl);
        $this->assertEquals(-3, $initial);
        $initial = $this->cache->decrement('key_decrement_memcached_float', 1.5, $this->ttl);
        $this->assertEquals(-4.5, $initial);
        $initial = $this->cache->decrement('key_decrement_memcached_float', 1, $this->ttl);
        $this->assertEquals(-5.5, $initial);
    }
}