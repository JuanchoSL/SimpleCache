<?php

namespace JuanchoSL\SimpleCache\Tests;

use JuanchoSL\SimpleCache\Contracts\SimpleCacheInterface;
use JuanchoSL\SimpleCache\Repositories\MemCache;
use PHPUnit\Framework\TestCase;
use JuanchoSL\SimpleCache\Adapters\SimpleCacheAdapter;

class MemcacheCacheTest extends TestCase
{
    private $cache;

    private $value_plain = 'value';
    private $value_array = ['value'];

    private $ttl = 10;

    public function setUp(): void
    {
        $this->cache = new SimpleCacheAdapter(new MemCache($_ENV['MEMCACHE_HOST']));
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
        $result = $this->cache->set('key_memcache', $this->value_plain, $this->ttl);
        $this->assertTrue($result);
    }
    public function testGetOk()
    {
        $result = $this->cache->set('key_memcache', $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        $read_ok = $this->cache->get('key_memcache');
        $this->assertEquals($this->value_plain, $read_ok);
    }
    public function testGetKo()
    {
        $result = $this->cache->set('key_memcache', $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        sleep($this->ttl + 1);
        $read_ko = $this->cache->get('key_memcache');
        $this->assertFalse($read_ko);
    }
    public function testTouch()
    {
        $result = $this->cache->set('key_memcache', $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        $read_ok = $this->cache->get('key_memcache');
        $this->assertEquals($this->value_plain, $read_ok);
        sleep(intval($this->ttl / 2));
        $touch = $this->cache->touch('key_memcache', $this->ttl);
        $this->assertTrue($touch);
        sleep(intval($this->ttl / 2) + 1);
        $read_ok = $this->cache->get('key_memcache');
        $this->assertEquals($this->value_plain, $read_ok);
    }
    public function testReplace()
    {
        $result = $this->cache->set('key_memcache', $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        $read_ok = $this->cache->get('key_memcache');
        $this->assertEquals($this->value_plain, $read_ok);
        $replace = $this->cache->replace('key_memcache', $this->value_plain . "-" . $this->value_plain);
        $this->assertTrue($replace);
        $read_ok = $this->cache->get('key_memcache');
        $this->assertEquals($this->value_plain . "-" . $this->value_plain, $read_ok);
    }
    public function testDelete()
    {
        $result = $this->cache->set('key_memcache', $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        $read_ok = $this->cache->get('key_memcache');
        $this->assertEquals($this->value_plain, $read_ok);
        $result = $this->cache->delete('key_memcache');
        $this->assertTrue($result);
        $read_ko = $this->cache->get('key_memcache');
        $this->assertFalse($read_ko);
    }
    /*public function testAllKeys()
    {
        $result = $this->cache->set('key_memcache', $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        $results = $this->cache->getAllKeys();
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $this->assertContains('key_memcache', $results);
    }*/

    public function testSetArray()
    {
        $result = $this->cache->set('array_memcache', ['key_memcache' => 'value'], $this->ttl);
        $this->assertTrue($result);
        $results = $this->cache->get('array_memcache');
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $this->assertArrayHasKey('key_memcache', $results);
        $this->assertEquals('value', $results['key_memcache']);
    }
    public function testSetObject()
    {
        $obj = new \stdClass;
        $obj->key = 'value';
        $result = $this->cache->set('object_memcache', $obj, $this->ttl);
        $this->assertTrue($result);
        $results = $this->cache->get('object_memcache');
        $this->assertIsObject($results);
        $this->assertObjectHasProperty('key', $results);
        $this->assertEquals('value', $results->key);
    }
    public function testIncrement()
    {
        $initial = $this->cache->increment('key_increment', 1, $this->ttl);
        $this->assertEquals(1, $initial);
        $initial = $this->cache->increment('key_increment', 1, $this->ttl);
        $this->assertEquals(2, $initial);
        $initial = $this->cache->increment('key_increment', 2, $this->ttl);
        $this->assertEquals(4, $initial);
    }

    public function testDecrement()
    {
        $initial = $this->cache->decrement('key_decrement', 1, $this->ttl);
        $this->assertEquals(-1, $initial);
        $initial = $this->cache->decrement('key_decrement', 1, $this->ttl);
        $this->assertEquals(-2, $initial);
        $initial = $this->cache->decrement('key_decrement', 2, $this->ttl);
        $this->assertEquals(-4, $initial);
    }
    public function testIncrementFloat()
    {
        $initial = $this->cache->increment('key_increment_float', 1.5, $this->ttl);
        $this->assertEquals(1.5, $initial);
        $initial = $this->cache->increment('key_increment_float', 1.5, $this->ttl);
        $this->assertEquals(3, $initial);
        $initial = $this->cache->increment('key_increment_float', 2, $this->ttl);
        $this->assertEquals(5, $initial);
    }

    public function testDecrementFloat()
    {
        $initial = $this->cache->decrement('key_decrement_float', 1.5, $this->ttl);
        $this->assertEquals(-1.5, $initial);
        $initial = $this->cache->decrement('key_decrement_float', 1.5, $this->ttl);
        $this->assertEquals(-3, $initial);
        $initial = $this->cache->decrement('key_decrement_float', 1.5, $this->ttl);
        $this->assertEquals(-4.5, $initial);
        $initial = $this->cache->decrement('key_decrement_float', 1, $this->ttl);
        $this->assertEquals(-5.5, $initial);
    }
}