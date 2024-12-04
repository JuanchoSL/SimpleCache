<?php

namespace JuanchoSL\SimpleCache\Tests\Integration;

use JuanchoSL\Logger\Composers\TextComposer;
use JuanchoSL\Logger\Logger;
use JuanchoSL\Logger\Repositories\FileRepository;
use JuanchoSL\SimpleCache\Enums\Engines;
use JuanchoSL\SimpleCache\Repositories\FileCache;
use JuanchoSL\SimpleCache\Repositories\MemCache;
use JuanchoSL\SimpleCache\Repositories\MemCached;
use JuanchoSL\SimpleCache\Repositories\ProcessCache;
use JuanchoSL\SimpleCache\Repositories\RedisCache;
use JuanchoSL\SimpleCache\Repositories\SessionCache;
use JuanchoSL\SimpleCache\Tests\Common\Credentials;
use PHPUnit\Framework\TestCase;

class LoggerRepositoryTest extends TestCase
{

    private $value_plain = 'value';
    private $value_array = ['value'];

    private $ttl = 5;

    private $file_path;
    protected function providerLoginData(): array
    {
        $debug = true;
        defined('TMPDIR') or define('TMPDIR', sys_get_temp_dir());

        $this->file_path = TMPDIR . DIRECTORY_SEPARATOR . 'error.log';
        $logger = new Logger((new FileRepository($this->file_path))->setComposer(new TextComposer));
        if (Credentials::GIT_MODE) {
            return ['Process' => [new ProcessCache(Credentials::getHost(Engines::PROCESS)), $logger, $debug]];
        }

        return [
            'Process' => [new ProcessCache(Credentials::getHost(Engines::PROCESS)), $logger, $debug],
            'Session' => [new SessionCache(Credentials::getHost(Engines::SESSION)), $logger, $debug],
            'File' => [new FileCache(Credentials::getHost(Engines::FILE)), $logger, $debug],
            'Memcache' => [new MemCache(Credentials::getHost(Engines::MEMCACHE)), $logger, $debug],
            'Memcached' => [new MemCached(Credentials::getHost(Engines::MEMCACHED)), $logger, $debug],
            'Redis' => [new RedisCache(Credentials::getHost(Engines::REDIS)), $logger, $debug],
        ];
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testSet($cache, $logger, $debug)
    {
        $cache->setLogger($logger);
        $cache->setDebug($debug);
        $name = str_replace('\\', '-', get_class($cache));
        $result = $cache->set("{$name}.key", $this->value_plain, $this->ttl);
        $this->assertTrue($result);
        $cache->clear();
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testGetOk($cache, $logger, $debug)
    {
        $cache->setLogger($logger);
        $cache->setDebug($debug);
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
    public function testGetKo($cache, $logger, $debug)
    {
        $cache->setLogger($logger);
        $cache->setDebug($debug);
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
    public function testTouch($cache, $logger, $debug)
    {
        $cache->setLogger($logger);
        $cache->setDebug($debug);
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
    public function testReplace($cache, $logger, $debug)
    {
        $cache->setLogger($logger);
        $cache->setDebug($debug);
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
    public function testDelete($cache, $logger, $debug)
    {
        $cache->setLogger($logger);
        $cache->setDebug($debug);
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
    public function testAllKeys($cache,$logger,$debug)
    {
        $cache->setLogger($logger);
        $cache->setDebug($debug);
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
    public function testSetArray($cache, $logger, $debug)
    {
        $cache->setLogger($logger);
        $cache->setDebug($debug);
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
    public function testSetObject($cache, $logger, $debug)
    {
        $cache->setLogger($logger);
        $cache->setDebug($debug);
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
    public function testIncrement($cache, $logger, $debug)
    {
        $cache->setLogger($logger);
        $cache->setDebug($debug);
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
    public function testDecrement($cache, $logger, $debug)
    {
        $cache->setLogger($logger);
        $cache->setDebug($debug);
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
    public function testIncrementFloat($cache, $logger, $debug)
    {
        $cache->setLogger($logger);
        $cache->setDebug($debug);
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
    public function testDecrementFloat($cache, $logger, $debug)
    {
        $cache->setLogger($logger);
        $cache->setDebug($debug);
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
    public function testSetMultiple($cache, $logger, $debug)
    {
        $cache->setLogger($logger);
        $cache->setDebug($debug);
        $name = str_replace('\\', '-', get_class($cache));
        $this->assertTrue($cache->setMultiple(["a" => "aa", "b" => "bb", "c" => "cc"], \DateInterval::createFromDateString("10 seconds")));
    }

    /**
     * @dataProvider providerLoginData
     */
    public function testGetMultiple($cache, $logger, $debug)
    {
        $cache->setLogger($logger);
        $cache->setDebug($debug);
        $name = str_replace('\\', '-', get_class($cache));
        $this->testSetMultiple($cache, $logger, $debug);
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
    public function testDeleteMultiple($cache, $logger, $debug)
    {
        $cache->setLogger($logger);
        $cache->setDebug($debug);
        $name = str_replace('\\', '-', get_class($cache));
        $this->testSetMultiple($cache, $logger, $debug);
        $keys = ["a", "b", "c"];
        $this->assertTrue($cache->deleteMultiple($keys));
        $cache->clear();
    }

    public function testDeleteLog()
    {
        defined('TMPDIR') or define('TMPDIR', sys_get_temp_dir());

        $file_path = TMPDIR . DIRECTORY_SEPARATOR . 'error.log';
        $this->assertFileExists($file_path);
        $this->assertTrue(unlink($file_path));
        $this->assertFileDoesNotExist($file_path);
    }
}