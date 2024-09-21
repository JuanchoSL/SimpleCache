<?php

namespace JuanchoSL\SimpleCache\Tests\Functional;

use DateInterval;
use JuanchoSL\SimpleCache\Adapters\PsrCacheItem;
use JuanchoSL\SimpleCache\Adapters\PsrCachePool;
use JuanchoSL\SimpleCache\Enums\Engines;
use JuanchoSL\SimpleCache\Factories\EngineFactory;
use JuanchoSL\SimpleCache\Tests\Common\Credentials;
use Psr\Cache\CacheItemPoolInterface;
use PHPUnit\Framework\TestCase;

class PoolCacheTest extends TestCase
{

    private $value_plain = 'value';
    private $value_array = ['value'];
    private $ttl = 5;

    protected function providerLoginData($cache): array
    {
        return [
            'Process' => [
                new PsrCachePool(EngineFactory::getInstance(Engines::PROCESS, Credentials::getHost(Engines::PROCESS)))
            ],
            'Session' => [
                new PsrCachePool(EngineFactory::getInstance(Engines::SESSION, Credentials::getHost(Engines::SESSION)))
            ],
            'File' => [
                new PsrCachePool(EngineFactory::getInstance(Engines::FILE, Credentials::getHost(Engines::FILE)))
            ],
            'Memcache' => [
                new PsrCachePool(EngineFactory::getInstance(Engines::MEMCACHE, Credentials::getHost(Engines::MEMCACHE)))
            ],
            'Memcached' => [
                new PsrCachePool(EngineFactory::getInstance(Engines::MEMCACHED, Credentials::getHost(Engines::MEMCACHED)))
            ],
            'Redis' => [
                new PsrCachePool(EngineFactory::getInstance(Engines::REDIS, Credentials::getHost(Engines::REDIS)))
            ],
        ];
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testLoad($cache)
    {
        $this->assertInstanceOf(CacheItemPoolInterface::class, $cache);
        // $this->assertInstanceOf(SimpleCacheInterface::class, $cache);
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testSet($cache)
    {
        $name = str_replace('\\','-',get_class($cache));
        $interval = DateInterval::createFromDateString("+{$this->ttl} seconds");
        $item = new PsrCacheItem("{$name}.key");
        $item = $item->set($this->value_plain)->expiresAfter($interval);
        $result = $cache->save($item);
        //$result = $cache->set("{$name}.key", $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testGetOk($cache)
    {
        $name = str_replace('\\','-',get_class($cache));
        $interval = DateInterval::createFromDateString("+{$this->ttl} seconds");
        $item = new PsrCacheItem("{$name}.key");
        $item = $item->set($this->value_plain)->expiresAfter($interval);
        $result = $cache->save($item);
        $this->assertTrue($result);
        $read_ok = $cache->getItem("{$name}.key");
        $this->assertEquals($this->value_plain, $read_ok->get($cache));
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testGetKo($cache)
    {
        $name = str_replace('\\','-',get_class($cache));
        $interval = DateInterval::createFromDateString("+{$this->ttl} seconds");
        $item = new PsrCacheItem("{$name}.key");
        $item = $item->set($this->value_plain)->expiresAfter($interval);
        $result = $cache->save($item);
        $this->assertTrue($result);
        sleep($this->ttl + 1);
        $read_ko = $cache->getItem("{$name}.key");
        $this->assertNull($read_ko->get());
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testDelete($cache)
    {
        $name = str_replace('\\','-',get_class($cache));
        $interval = DateInterval::createFromDateString("+{$this->ttl} seconds");
        $item = new PsrCacheItem("{$name}.key");
        $item = $item->set($this->value_plain)->expiresAfter($interval);
        $result = $cache->save($item);
        $this->assertTrue($result);
        $read_ok = $cache->getItem("{$name}.key");
        $this->assertEquals($this->value_plain, $read_ok->get());
        $result = $cache->deleteItem("{$name}.key");
        $this->assertTrue($result);
        $read_ko = $cache->getItem("{$name}.key");
        $this->assertNull($read_ko->get());
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testSetArray($cache)
    {
        $name = str_replace('\\','-',get_class($cache));
        $item = new PsrCacheItem("{$name}.array");
        $item = $item->set(['key' => 'value'])->expiresAfter($this->ttl);
        $result = $cache->save($item);
        $this->assertTrue($result);
        $results = $cache->getItem("{$name}.array")->get($cache);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $this->assertArrayHasKey('key', $results);
        $this->assertEquals('value', $results['key']);
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testSetObject($cache)
    {
        $name = str_replace('\\','-',get_class($cache));
        $obj = new \stdClass;
        $obj->key = 'value';
        $item = new PsrCacheItem("{$name}.object");
        $item = $item->set($obj)->expiresAfter($this->ttl);
        $result = $cache->save($item);
        $this->assertTrue($result);
        $results = $cache->getItem("{$name}.object")->get();
        $this->assertIsObject($results);
        $this->assertObjectHasProperty('key', $results);
        $this->assertEquals('value', $results->key);
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testSetMultiple($cache)
    {
        $vars = ["a" => "aa", "b" => "bb", "c" => "cc"];
        foreach ($vars as $key => $value) {
            $item = new PsrCacheItem($key);
            $item = $item->set($value)->expiresAfter(DateInterval::createFromDateString("3 seconds"));
            $this->assertTrue($cache->saveDeferred($item));
        }
        $this->assertTrue($cache->commit());
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testGetMultipleOk($cache)
    {
        $this->testSetMultiple($cache);
        $keys = ["a", "b", "c"];
        $results = $cache->getItems($keys);
        foreach ($keys as $key) {
            $this->assertTrue($results[$key]->isHit());
            $this->assertEquals($key . $key, $results[$key]->get());
        }
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testGetMultipleKo($cache)
    {
        $this->testSetMultiple($cache);
        sleep(3);
        $keys = ["a", "b", "c"];
        $results = $cache->getItems($keys);
        foreach ($keys as $key) {
            $this->assertFalse($results[$key]->isHit());
            $this->assertNull($results[$key]->get());
        }
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testDeleteMultiple($cache)
    {
        $this->testSetMultiple($cache);
        $keys = ["a", "b", "c"];
        $this->assertTrue($cache->deleteItems($keys));
        foreach ($keys as $key) {
            $this->assertFalse($cache->hasItem($key));
        }
        $cache->clear();
    }
}