<?php

namespace JuanchoSL\SimpleCache\Tests\Unit;

use JuanchoSL\SimpleCache\Enums\Engines;
use JuanchoSL\SimpleCache\Factories\EngineFactory;
use JuanchoSL\SimpleCache\Repositories\FileCache;
use JuanchoSL\SimpleCache\Repositories\MemCache;
use JuanchoSL\SimpleCache\Repositories\MemCached;
use JuanchoSL\SimpleCache\Repositories\ProcessCache;
use JuanchoSL\SimpleCache\Repositories\RedisCache;
use JuanchoSL\SimpleCache\Repositories\SessionCache;
use JuanchoSL\SimpleCache\Tests\Common\Credentials;
use PHPUnit\Framework\TestCase;

class RepositoryTest extends TestCase
{

    private $value_plain = 'value';
    private $value_array = ['value'];

    private $ttl = 5;


    protected function providerLoginData(): array
    {
        if (Credentials::GIT_MODE) {
            return ['Process' => [new ProcessCache(Credentials::getHost(Engines::PROCESS))]];
        }
        return [
            'Process' => [
                new ProcessCache(Credentials::getHost(Engines::PROCESS))
            ],
            'Session' => [
                new SessionCache(Credentials::getHost(Engines::SESSION))
            ],
            'File' => [
                new FileCache(Credentials::getHost(Engines::FILE))
            ],
            'Memcache' => [
                new MemCache(Credentials::getHost(Engines::MEMCACHE))
            ],
            'Memcached' => [
                new MemCached(Credentials::getHost(Engines::MEMCACHED))
            ],
            'Redis' => [
                new RedisCache(Credentials::getHost(Engines::REDIS))
            ],
        ];
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testSet($cache)
    {
        $name = str_replace('\\', '-', get_class($cache));
        $result = $cache->set("{$name}.key", $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testGetOk($cache)
    {
        $name = str_replace('\\', '-', get_class($cache));
        $result = $cache->set("{$name}.key", $this->value_plain, $this->ttl);
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
        $result = $cache->set("{$name}.key", $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        sleep($this->ttl + 1);
        $read_ko = $cache->get("{$name}.key");
        $this->assertNull($read_ko);
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testTouch($cache)
    {
        $name = str_replace('\\', '-', get_class($cache));
        $result = $cache->set("{$name}.key", $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        $read_ok = $cache->get("{$name}.key");
        $this->assertEquals($this->value_plain, $read_ok);
        sleep(intval($this->ttl / 2));
        $touch = $cache->touch("{$name}.key", $this->ttl);
        $this->assertTrue($touch);
        sleep(intval($this->ttl / 2) + 1);
        $read_ok = $cache->get("{$name}.key");
        $this->assertEquals($this->value_plain, $read_ok);
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testReplace($cache)
    {
        $name = str_replace('\\', '-', get_class($cache));
        $result = $cache->set("{$name}.key", $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        $read_ok = $cache->get("{$name}.key");
        $this->assertEquals($this->value_plain, $read_ok);
        $replace = $cache->replace("{$name}.key", $this->value_plain . "-" . $this->value_plain);
        $this->assertTrue($replace);
        $read_ok = $cache->get("{$name}.key");
        $this->assertEquals($this->value_plain . "-" . $this->value_plain, $read_ok);
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
    /*
    public function testAllKeys($cache)
    {
        $result = $cache->set("{$name}.key", $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        $results = $cache->getAllKeys($cache);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $this->assertContains("{$name}.key", $results);
        print_r($result);
        $cache->clear();
        }
        */
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
    public function testIncrement($cache)
    {
        $name = str_replace('\\', '-', get_class($cache));
        $initial = $cache->increment("{$name}.key_increment", 1, $this->ttl);
        $this->assertEquals(1, $initial);
        $initial = $cache->increment("{$name}.key_increment", 1, $this->ttl);
        $this->assertEquals(2, $initial);
        $initial = $cache->increment("{$name}.key_increment", 2, $this->ttl);
        $this->assertEquals(4, $initial);
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testDecrement($cache)
    {
        $name = str_replace('\\', '-', get_class($cache));
        $initial = $cache->decrement("{$name}.key_decrement", 1, $this->ttl);
        $this->assertEquals(-1, $initial);
        $initial = $cache->decrement("{$name}.key_decrement", 1, $this->ttl);
        $this->assertEquals(-2, $initial);
        $initial = $cache->decrement("{$name}.key_decrement", 2, $this->ttl);
        $this->assertEquals(-4, $initial);
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testIncrementFloat($cache)
    {
        $name = str_replace('\\', '-', get_class($cache));
        $initial = $cache->increment("{$name}.key_increment_float", 1.5, $this->ttl);
        $this->assertEquals(1.5, $initial);
        $initial = $cache->increment("{$name}.key_increment_float", 1.5, $this->ttl);
        $this->assertEquals(3, $initial);
        $initial = $cache->increment("{$name}.key_increment_float", 2, $this->ttl);
        $this->assertEquals(5, $initial);
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testDecrementFloat($cache)
    {
        $name = str_replace('\\', '-', get_class($cache));
        $initial = $cache->decrement("{$name}.key_decrement_float", 1.5, $this->ttl);
        $this->assertEquals(-1.5, $initial);
        $initial = $cache->decrement("{$name}.key_decrement_float", 1.5, $this->ttl);
        $this->assertEquals(-3, $initial);
        $initial = $cache->decrement("{$name}.key_decrement_float", 1.5, $this->ttl);
        $this->assertEquals(-4.5, $initial);
        $initial = $cache->decrement("{$name}.key_decrement_float", 1, $this->ttl);
        $this->assertEquals(-5.5, $initial);
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testSetMultiple($cache)
    {
        $name = str_replace('\\', '-', get_class($cache));
        $this->assertTrue($cache->setMultiple(["a" => "aa", "b" => "bb", "c" => "cc"], \DateInterval::createFromDateString("10 seconds")));
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testGetMultiple($cache)
    {
        $name = str_replace('\\', '-', get_class($cache));
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
        $name = str_replace('\\', '-', get_class($cache));
        $this->testSetMultiple($cache);
        $keys = ["a", "b", "c"];
        $this->assertTrue($cache->deleteMultiple($keys));
        $cache->clear();
    }
}