<?php

namespace JuanchoSL\SimpleCache\Tests\Unit;

use JuanchoSL\Exceptions\PreconditionFailedException;
use JuanchoSL\SimpleCache\Enums\Engines;
use JuanchoSL\SimpleCache\Repositories\ApcuCache;
use JuanchoSL\SimpleCache\Repositories\FileCache;
use JuanchoSL\SimpleCache\Repositories\MemCache;
use JuanchoSL\SimpleCache\Repositories\MemCached;
use JuanchoSL\SimpleCache\Repositories\ProcessCache;
use JuanchoSL\SimpleCache\Repositories\RedisCache;
use JuanchoSL\SimpleCache\Repositories\SessionCache;
use JuanchoSL\SimpleCache\Repositories\YacCache;
use JuanchoSL\SimpleCache\Tests\Common\Credentials;
use PHPUnit\Framework\TestCase;
use stdClass;

class RepositoryTest extends TestCase
{

    private $value_plain = 'value';
    private $value_array = ['value'];

    private $ttl = 5;


    public static function providerLoginData(): array
    {
        if (Credentials::GIT_MODE) {
            return [
                'Process' => [new ProcessCache(Credentials::getHost(Engines::PROCESS))],
                'File' => [new FileCache(Credentials::getHost(Engines::FILE))],
                'Session' => [new SessionCache(Credentials::getHost(Engines::SESSION))],
                'Yac' => [new YacCache(Credentials::getHost(Engines::YAC))],
                'Apcu' => [new ApcuCache()],
                'Redis' => [new RedisCache(Credentials::getHost(Engines::REDIS))],
                'Memcached' => [new MemCached(Credentials::getHost(Engines::MEMCACHED))],
            ];
        }
        return [
            'Process' => [new ProcessCache(Credentials::getHost(Engines::PROCESS))],
            'Session' => [new SessionCache(Credentials::getHost(Engines::SESSION))],
            'File' => [new FileCache(Credentials::getHost(Engines::FILE))],
            'Memcache' => [new MemCache(Credentials::getHost(Engines::MEMCACHE))],
            'Memcached' => [new MemCached(Credentials::getHost(Engines::MEMCACHED))],
            'Redis' => [new RedisCache(Credentials::getHost(Engines::REDIS))],
            'Yac' => [new YacCache(Credentials::getHost(Engines::YAC))],
            'Apcu' => [new ApcuCache()],
        ];
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testSet($cache)
    {
        $name = str_replace('\\', '-', get_class($cache));
        $result = $cache->set(md5($name) . ".key", $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testGetOk($cache)
    {
        $name = str_replace('\\', '-', get_class($cache));
        $result = $cache->set(md5($name) . ".key", $this->value_plain, $this->ttl);
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
        $name = str_replace('\\', '-', get_class($cache));
        $result = $cache->set(md5($name) . ".key", $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        sleep($this->ttl + 1);
        $read_ko = $cache->get(md5($name) . ".key");
        $this->assertNull($read_ko);
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testTouch($cache)
    {
        $name = str_replace('\\', '-', get_class($cache));
        $result = $cache->set(md5($name) . ".key", $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        $read_ok = $cache->get(md5($name) . ".key");
        $this->assertEquals($this->value_plain, $read_ok);
        sleep(intval($this->ttl / 2));
        $touch = $cache->touch(md5($name) . ".key", $this->ttl);
        $this->assertTrue($touch);
        sleep(intval($this->ttl / 2) + 1);
        $read_ok = $cache->get(md5($name) . ".key");
        $this->assertEquals($this->value_plain, $read_ok);
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testReplace($cache)
    {
        $name = str_replace('\\', '-', get_class($cache));
        $result = $cache->set(md5($name) . ".key", $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        $read_ok = $cache->get(md5($name) . ".key");
        $this->assertEquals($this->value_plain, $read_ok);
        $replace = $cache->replace(md5($name) . ".key", $this->value_plain . "-" . $this->value_plain);
        $this->assertTrue($replace);
        $read_ok = $cache->get(md5($name) . ".key");
        $this->assertEquals($this->value_plain . "-" . $this->value_plain, $read_ok);
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testDelete($cache)
    {
        $name = str_replace('\\', '-', get_class($cache));
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
    /*
    public function testAllKeys($cache)
    {
        $result = $cache->set(md5($name).".key", $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        $results = $cache->getAllKeys($cache);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $this->assertContains(md5($name).".key", $results);
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
        $name = str_replace('\\', '-', get_class($cache));
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
    public function testIncrement($cache)
    {
        $name = str_replace('\\', '-', get_class($cache));
        $initial = $cache->increment(md5($name) . ".ki", 1, $this->ttl);
        $this->assertEquals(1, $initial);
        $initial = $cache->increment(md5($name) . ".ki", 1, $this->ttl);
        $this->assertEquals(2, $initial);
        $initial = $cache->increment(md5($name) . ".ki", 2, $this->ttl);
        $this->assertEquals(4, $initial);
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testDecrement($cache)
    {
        $name = str_replace('\\', '-', get_class($cache));
        $initial = $cache->decrement(md5($name) . ".kd", 1, $this->ttl);
        $this->assertEquals(-1, $initial);
        $initial = $cache->decrement(md5($name) . ".kd", 1, $this->ttl);
        $this->assertEquals(-2, $initial);
        $initial = $cache->decrement(md5($name) . ".kd", 2, $this->ttl);
        $this->assertEquals(-4, $initial);
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testIncrementFloat($cache)
    {
        $name = str_replace('\\', '-', get_class($cache));
        $initial = $cache->increment(md5($name) . ".kif", 1.5, $this->ttl);
        $this->assertEquals(1.5, $initial);
        $initial = $cache->increment(md5($name) . ".kif", 1.5, $this->ttl);
        $this->assertEquals(3, $initial);
        $initial = $cache->increment(md5($name) . ".kif", 2, $this->ttl);
        $this->assertEquals(5, $initial);
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testDecrementFloat($cache)
    {
        $name = str_replace('\\', '-', get_class($cache));
        $initial = $cache->decrement(md5($name) . ".kdf", 1.5, $this->ttl);
        $this->assertEquals(-1.5, $initial);
        $initial = $cache->decrement(md5($name) . ".kdf", 1.5, $this->ttl);
        $this->assertEquals(-3, $initial);
        $initial = $cache->decrement(md5($name) . ".kdf", 1.5, $this->ttl);
        $this->assertEquals(-4.5, $initial);
        $initial = $cache->decrement(md5($name) . ".kdf", 1, $this->ttl);
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
        $name = str_replace('\\', '-', get_class($cache));
        $this->testSetMultiple($cache);
        $keys = ["a", "b", "c"];
        $this->assertTrue($cache->deleteMultiple($keys));
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testRetrieveDefault($cache)
    {
        $name = str_replace('\\', '-', get_class($cache));
        //$result = $cache->set(md5($name).".array", ['key' => 'value'], $this->ttl);
        //$this->assertTrue($result);

        $results = $cache->get(md5($name) . ".array");
        $this->assertEmpty($results);
        $this->assertIsNotArray($results);

        $results = $cache->get(md5($name) . ".array", ['key' => 'value']);
        $this->assertNotEmpty($results);
        $this->assertIsArray($results);
        $this->assertArrayHasKey('key', $results);
        $this->assertEquals('value', $results['key']);

        $results = $cache->get(md5($name) . ".array", function () {
            return ['key' => 'value'];
        });
        $this->assertNotEmpty($results);
        $this->assertIsArray($results);
        $this->assertArrayHasKey('key', $results);
        $this->assertEquals('value', $results['key']);

        $std = new stdClass();
        $std->key = 'value';
        $results = $cache->get(md5($name) . ".array", $std);
        $this->assertNotEmpty($results);
        $this->assertIsObject($results);
        $this->assertObjectHasProperty('key', $results);
        $this->assertEquals('value', $results->key);

        $results = $cache->get(md5($name) . ".array", function () {
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


    /**
     * @dataProvider providerLoginData
     */
    public function testInvalidKeySet($cache)
    {
        $this->expectException(\InvalidArgumentException::class);
        $cache->set("algo@", 'some data', 1);
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testInvalidKeyGet($cache)
    {
        $this->expectException(\InvalidArgumentException::class);
        $cache->get("algo@");
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testInvalidKeyDelete($cache)
    {
        $this->expectException(\InvalidArgumentException::class);
        $cache->delete("algo@");
    }
    /**
     * @dataProvider providerLoginData
     */
    public function testInvalidKeySetMultiple($cache)
    {
        $this->expectException(\InvalidArgumentException::class);
        $cache->setMultiple(["algo@" => 'some data'], 1);
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testInvalidKeyGetMultiple($cache)
    {
        $this->expectException(\InvalidArgumentException::class);
        $cache->getMultiple(["algo@"]);
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testInvalidKeyDeleteMultiple($cache)
    {
        $this->expectException(\InvalidArgumentException::class);
        $cache->deleteMultiple(["algo@"]);
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testInvalidKeyChars($cache)
    {
        $this->expectException(\InvalidArgumentException::class);
        $cache->setExtraChars('@');
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testValidKey($cache)
    {
        $cache->setExtraChars('-');
        $cache->set("algo-", 'some data', 10);
        $result = $cache->get("algo-");
        $this->assertEquals('some data', $result);
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testInvalidKeyLenght($cache)
    {
        $this->expectException(PreconditionFailedException::class);
        $cache->setMaxKeyLenght(25);
    }

}