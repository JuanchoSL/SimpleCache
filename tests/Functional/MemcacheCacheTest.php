<?php

namespace JuanchoSL\SimpleCache\Tests\Functional;

use JuanchoSL\SimpleCache\Repositories\MemCache;
use PHPUnit\Framework\TestCase;
use JuanchoSL\SimpleCache\Adapters\PsrSimpleCacheAdapter;
use Psr\SimpleCache\CacheInterface;

class MemcacheCacheTest extends TestCase
{
    private $cache;

    private $value_plain = 'value';
    private $value_array = ['value'];

    private $ttl = 10;

    public function setUp(): void
    {
        $this->cache = new PsrSimpleCacheAdapter(new MemCache($_ENV['MEMCACHE_HOST']));
    }
    public function tearDown(): void
    {
        $this->cache->clear();
    }
    public function testLoad()
    {
        $this->assertInstanceOf(CacheInterface::class, $this->cache);
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
        $this->assertNull($read_ko);
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
        $this->assertNull($read_ko);
    }

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

}