<?php

namespace JuanchoSL\SimpleCache\Tests\Functional;

use DateInterval;
use JuanchoSL\Exceptions\PreconditionFailedException;
use JuanchoSL\SimpleCache\Adapters\PsrSimpleCacheAdapter;
use JuanchoSL\SimpleCache\Enums\Engines;
use JuanchoSL\SimpleCache\Factories\EngineFactory;
use JuanchoSL\SimpleCache\Tests\Common\Credentials;
use Psr\SimpleCache\CacheInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

class SimpleCacheTest extends TestCase
{

    private $value_plain = 'value';
    private $value_array = ['value'];
    private $ttl = 5;

    public static function providerLoginData($cache): array
    {
        if (Credentials::GIT_MODE) {
            return [
                'Process' => [new PsrSimpleCacheAdapter(EngineFactory::getInstance(Engines::PROCESS, Credentials::getHost(Engines::PROCESS)))],
                'File' => [new PsrSimpleCacheAdapter(EngineFactory::getInstance(Engines::FILE, Credentials::getHost(Engines::FILE)))],
                'Yac' => [new PsrSimpleCacheAdapter(EngineFactory::getInstance(Engines::YAC, Credentials::getHost(Engines::YAC)))],
                'Apcu' => [new PsrSimpleCacheAdapter(EngineFactory::getInstance(Engines::APCU, ''))]
            ];
        }
        return [
            'Process' => [new PsrSimpleCacheAdapter(EngineFactory::getInstance(Engines::PROCESS, Credentials::getHost(Engines::PROCESS)))],
            'Session' => [new PsrSimpleCacheAdapter(EngineFactory::getInstance(Engines::SESSION, Credentials::getHost(Engines::SESSION)))],
            'File' => [new PsrSimpleCacheAdapter(EngineFactory::getInstance(Engines::FILE, Credentials::getHost(Engines::FILE)))],
            'Memcache' => [new PsrSimpleCacheAdapter(EngineFactory::getInstance(Engines::MEMCACHE, Credentials::getHost(Engines::MEMCACHE)))],
            'Memcached' => [new PsrSimpleCacheAdapter(EngineFactory::getInstance(Engines::MEMCACHED, Credentials::getHost(Engines::MEMCACHED)))],
            'Redis' => [new PsrSimpleCacheAdapter(EngineFactory::getInstance(Engines::REDIS, Credentials::getHost(Engines::REDIS)))],
            'Yac' => [new PsrSimpleCacheAdapter(EngineFactory::getInstance(Engines::YAC, Credentials::getHost(Engines::YAC)))],
            'Apcu' => [new PsrSimpleCacheAdapter(EngineFactory::getInstance(Engines::APCU, ''))]
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
        $name = str_replace('\\', '_', get_class($cache));
        $interval = DateInterval::createFromDateString("+{$this->ttl} seconds");
        $result = $cache->set(md5($name) . ".key", $this->value_plain, $interval);
        //$result = $cache->set(md5($name).".key", $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testGetOk($cache)
    {
        $name = str_replace('\\', '_', get_class($cache));
        $interval = DateInterval::createFromDateString("+{$this->ttl} seconds");
        $result = $cache->set(md5($name) . ".key", $this->value_plain, $interval);
        //$result = $cache->set(md5($name).".key", $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        $read_ok = $cache->get(md5($name) . ".key");
        $this->assertEquals($this->value_plain, $read_ok);
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testGetKo($cache)
    {
        $name = str_replace('\\', '_', get_class($cache));
        $interval = DateInterval::createFromDateString("+{$this->ttl} seconds");
        $result = $cache->set(md5($name) . ".key", $this->value_plain, $interval);
        //$result = $cache->set(md5($name).".key", $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        sleep($this->ttl + 1);
        $read_ko = $cache->get(md5($name) . ".key");
        $this->assertNull($read_ko);
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testDelete($cache)
    {
        $name = str_replace('\\', '_', get_class($cache));
        $result = $cache->set(md5($name) . ".key", $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        $read_ok = $cache->get(md5($name) . ".key");
        $this->assertEquals($this->value_plain, $read_ok);
        $result = $cache->delete(md5($name) . ".key");
        $this->assertTrue($result);
        $read_ko = $cache->get(md5($name) . ".key");
        $this->assertNull($read_ko);
        $cache->clear();
    }


    /**
     * @dataProvider providerLoginData
     */
    public function testSetArray($cache)
    {
        $name = str_replace('\\', '_', get_class($cache));
        $result = $cache->set(md5($name) . ".array", ['key' => 'value'], $this->ttl);
        $this->assertTrue($result);
        $results = $cache->get(md5($name) . ".array");
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
        $name = str_replace('\\', '_', get_class($cache));
        $obj = new stdClass;
        $obj->key = 'value';
        $result = $cache->set(md5($name) . ".obj", $obj, $this->ttl);
        $this->assertTrue($result);
        $results = $cache->get(md5($name) . ".obj");
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
    public function testGetMultipleDefault($cache)
    {
        $cache->clear();
        $keys = ["a", "b", "c"];

        $results = $cache->getMultiple($keys);
        $this->assertEmpty($results);
        /*
         */
        $results = $cache->getMultiple($keys, false);
        $this->assertNotEmpty($results);
        foreach ($keys as $key) {
            $this->assertEmpty($results[$key]);
        }
        $defaults = [];
        foreach ($keys as $i => $key) {
            $defaults[$i] = $key . $key;
        }
        $results = $cache->getMultiple($keys, $defaults);
        $this->assertNotEmpty($results);
        foreach ($keys as $key) {
            $this->assertEquals($key . $key, $results[$key]);
        }

        $defaults = [];
        foreach ($keys as $i => $key) {
            $defaults[$i] = (function () use ($key) {
                return $key . $key;
            });
        }

        $results = $cache->getMultiple($keys, $defaults);
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

    /**
     * @dataProvider providerLoginData
     */
    public function testInvalidKey($cache)
    {
        $this->expectException(\InvalidArgumentException::class);
        $cache->set("algo@", 'some data', 1);
    }
    /**
     * @dataProvider providerLoginData
     */
    public function testValidKey($cache)
    {
        $cache->setExtraChars('@');
        $cache->set("algo@", 'some data', 10);
        $result = $cache->get("algo@");
        $this->assertEquals('some data', $result);
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testInvalidKeyLenght($cache)
    {
        $this->expectException(PreconditionFailedException::class);
        $cache->setMaxKeyLenght(25);
    }


    /**
     * @dataProvider providerLoginData
     */
    public function testRetrieveDefault($cache)
    {
        $name = md5(str_replace('\\', '-', get_class($cache)));

        $results = $cache->get(md5($name) . ".key");
        $this->assertEmpty($results);
        $this->assertIsNotArray($results);

        $results = $cache->get(md5($name) . ".key", ['key' => 'value']);
        $this->assertNotEmpty($results);
        $this->assertIsArray($results);
        $this->assertArrayHasKey('key', $results);
        $this->assertEquals('value', $results['key']);

        $results = $cache->get(md5($name) . ".key", function () {
            return ['key' => 'value'];
        });
        $this->assertNotEmpty($results);
        $this->assertIsArray($results);
        $this->assertArrayHasKey('key', $results);
        $this->assertEquals('value', $results['key']);

        $std = new stdClass();
        $std->key = 'value';
        $results = $cache->get(md5($name) . ".key", $std);
        $this->assertNotEmpty($results);
        $this->assertIsObject($results);
        $this->assertObjectHasProperty('key', $results);
        $this->assertEquals('value', $results->key);

        $results = $cache->get(md5($name) . ".key", function () {
            $std = new stdClass();
            $std->key = 'value';
            return $std;
        });
        $this->assertNotEmpty($results);
        $this->assertIsObject($results);
        $this->assertObjectHasProperty('key', $results);
        $this->assertEquals('value', $results->key);

        $cache->clear();
    }
}