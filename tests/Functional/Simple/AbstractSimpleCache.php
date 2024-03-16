<?php

namespace JuanchoSL\SimpleCache\Tests\Functional\Simple;

use DateInterval;
use JuanchoSL\SimpleCache\Adapters\PsrSimpleCacheAdapter;
use JuanchoSL\SimpleCache\Enums\Engines;
use JuanchoSL\SimpleCache\Factories\EngineFactory;
use JuanchoSL\SimpleCache\Tests\Common\Credentials;
use Psr\SimpleCache\CacheInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractSimpleCache extends TestCase
{
    protected $cache;

    private $value_plain = 'value';
    private $value_array = ['value'];

    private $ttl = 5;

    abstract public function getEngine():Engines;

    public function setUp(): void
    {
        $this->cache = new PsrSimpleCacheAdapter(EngineFactory::getInstance($this->getEngine(), Credentials::getHost($this->getEngine())));
        //$this->cache = new PsrSimpleCacheAdapter(static::getCacheType());
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
        $interval = DateInterval::createFromDateString("+{$this->ttl} seconds");
        $result = $this->cache->set('key', $this->value_plain, $interval);
        //$result = $this->cache->set('key', $this->value_plain, $this->ttl);
        $this->assertTrue($result);
    }
    public function testGetOk()
    {
        $interval = DateInterval::createFromDateString("+{$this->ttl} seconds");
        $result = $this->cache->set('key', $this->value_plain, $interval);
        //$result = $this->cache->set('key', $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        $read_ok = $this->cache->get('key');
        $this->assertEquals($this->value_plain, $read_ok);
    }
    public function testGetKo()
    {
        $interval = DateInterval::createFromDateString("+{$this->ttl} seconds");
        $result = $this->cache->set('key', $this->value_plain, $interval);
        //$result = $this->cache->set('key', $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        sleep($this->ttl + 1);
        $read_ko = $this->cache->get('key');
        $this->assertNull($read_ko);
    }
    public function testDelete()
    {
        $result = $this->cache->set('key', $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        $read_ok = $this->cache->get('key');
        $this->assertEquals($this->value_plain, $read_ok);
        $result = $this->cache->delete('key');
        $this->assertTrue($result);
        $read_ko = $this->cache->get('key');
        $this->assertNull($read_ko);
    }

    public function testSetArray()
    {
        $result = $this->cache->set('array', ['key' => 'value'], $this->ttl);
        $this->assertTrue($result);
        $results = $this->cache->get('array');
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $this->assertArrayHasKey('key', $results);
        $this->assertEquals('value', $results['key']);
    }
    public function testSetObject()
    {
        $obj = new \stdClass;
        $obj->key = 'value';
        $result = $this->cache->set('object', $obj, $this->ttl);
        $this->assertTrue($result);
        $results = $this->cache->get('object');
        $this->assertIsObject($results);
        $this->assertObjectHasProperty('key', $results);
        $this->assertEquals('value', $results->key);
    }

    public function testSetMultiple()
    {
        $this->assertTrue($this->cache->setMultiple(["a" => "aa", "b" => "bb", "c" => "cc"], DateInterval::createFromDateString("10 seconds")));
    }

    public function testGetMultiple()
    {
        $this->testSetMultiple();
        $keys = ["a", "b", "c"];
        $results = $this->cache->getMultiple($keys);
        foreach ($keys as $key) {
            $this->assertEquals($key . $key, $results[$key]);
        }
    }

    public function testDeleteMultiple()
    {
        $this->testSetMultiple();
        $keys = ["a", "b", "c"];
        $this->assertTrue($this->cache->deleteMultiple($keys));
    }
}