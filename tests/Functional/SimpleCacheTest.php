<?php

namespace JuanchoSL\SimpleCache\Tests\Functional;

use DateInterval;
use JuanchoSL\SimpleCache\Adapters\PsrSimpleCacheAdapter;
use JuanchoSL\SimpleCache\Enums\Engines;
use JuanchoSL\SimpleCache\Factories\EngineFactory;
use JuanchoSL\SimpleCache\Tests\Common\Credentials;
use Psr\SimpleCache\CacheInterface;
use PHPUnit\Framework\TestCase;

class SimpleCacheTest extends TestCase
{

    private $value_plain = 'value';
    private $value_array = ['value'];
    private $ttl = 5;

    protected function providerLoginData($cache): array
    {
        return [
            'Process' => [
                new PsrSimpleCacheAdapter(EngineFactory::getInstance(Engines::PROCESS, Credentials::getHost(Engines::PROCESS)))
            ],
            'Session' => [
                new PsrSimpleCacheAdapter(EngineFactory::getInstance(Engines::SESSION, Credentials::getHost(Engines::SESSION)))
            ],
            'File' => [
                new PsrSimpleCacheAdapter(EngineFactory::getInstance(Engines::FILE, Credentials::getHost(Engines::FILE)))
            ],
            'Memcache' => [
                new PsrSimpleCacheAdapter(EngineFactory::getInstance(Engines::MEMCACHE, Credentials::getHost(Engines::MEMCACHE)))
            ],
            'Memcached' => [
                new PsrSimpleCacheAdapter(EngineFactory::getInstance(Engines::MEMCACHED, Credentials::getHost(Engines::MEMCACHED)))
            ],
            'Redis' => [
                new PsrSimpleCacheAdapter(EngineFactory::getInstance(Engines::REDIS, Credentials::getHost(Engines::REDIS)))
            ],
        ];
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testLoad($cache)
    {
        $this->assertInstanceOf(CacheInterface::class, $cache);
        // $this->assertInstanceOf(SimpleCacheInterface::class, $cache);
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testSet($cache)
    {
        $name = str_replace('\\', '-', get_class($cache));
        $interval = DateInterval::createFromDateString("+{$this->ttl} seconds");
        $result = $cache->set("{$name}.key", $this->value_plain, $interval);
        //$result = $cache->set("{$name}.key", $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testGetOk($cache)
    {
        $name = str_replace('\\', '-', get_class($cache));
        $interval = DateInterval::createFromDateString("+{$this->ttl} seconds");
        $result = $cache->set("{$name}.key", $this->value_plain, $interval);
        //$result = $cache->set("{$name}.key", $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        $read_ok = $cache->get("{$name}.key");
        $this->assertEquals($this->value_plain, $read_ok);
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testGetKo($cache)
    {
        $name = str_replace('\\', '-', get_class($cache));
        $interval = DateInterval::createFromDateString("+{$this->ttl} seconds");
        $result = $cache->set("{$name}.key", $this->value_plain, $interval);
        //$result = $cache->set("{$name}.key", $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        sleep($this->ttl + 1);
        $read_ko = $cache->get("{$name}.key");
        $this->assertNull($read_ko);
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testDelete($cache)
    {
        $name = str_replace('\\', '-', get_class($cache));
        $result = $cache->set("{$name}.key", $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        $read_ok = $cache->get("{$name}.key");
        $this->assertEquals($this->value_plain, $read_ok);
        $result = $cache->delete("{$name}.key");
        $this->assertTrue($result);
        $read_ko = $cache->get("{$name}.key");
        $this->assertNull($read_ko);
        $cache->clear();
    }


    /**
     * @dataProvider providerLoginData
     */
    public function testSetArray($cache)
    {
        $name = str_replace('\\', '-', get_class($cache));
        $result = $cache->set("{$name}.array", ['key' => 'value'], $this->ttl);
        $this->assertTrue($result);
        $results = $cache->get("{$name}.array");
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
        $name = str_replace('\\', '-', get_class($cache));
        $obj = new \stdClass;
        $obj->key = 'value';
        $result = $cache->set("{$name}.object", $obj, $this->ttl);
        $this->assertTrue($result);
        $results = $cache->get("{$name}.object");
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
        $this->assertTrue($cache->setMultiple(["a" => "aa", "b" => "bb", "c" => "cc"], DateInterval::createFromDateString("10 seconds")));
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testGetMultiple($cache)
    {
        $this->testSetMultiple($cache);
        $keys = ["a", "b", "c"];
        $results = $cache->getMultiple($keys);
        foreach ($keys as $key) {
            $this->assertEquals($key . $key, $results[$key]);
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
        $this->assertTrue($cache->deleteMultiple($keys));
        $cache->clear();
    }
}