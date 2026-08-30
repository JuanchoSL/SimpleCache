<?php

namespace JuanchoSL\SimpleCache\Tests\Integration;

use JuanchoSL\SimpleCache\Adapters\PsrSimpleCacheAdapter;
use JuanchoSL\SimpleCache\Enums\Engines;
use JuanchoSL\SimpleCache\Factories\EngineFactory;
use JuanchoSL\SimpleCache\Tests\Common\Credentials;
use PHPUnit\Framework\TestCase;

class PerformanceCacheTest extends TestCase
{


    public function testPerformance()
    {
        $this->markTestSkipped();

        $origins = [
            'Process' => [new PsrSimpleCacheAdapter(EngineFactory::getInstance(Engines::PROCESS, Credentials::getHost(Engines::PROCESS)))],
            'Session' => [new PsrSimpleCacheAdapter(EngineFactory::getInstance(Engines::SESSION, Credentials::getHost(Engines::SESSION)))],
            'File' => [new PsrSimpleCacheAdapter(EngineFactory::getInstance(Engines::FILE, Credentials::getHost(Engines::FILE)))],
            'Memcache' => [new PsrSimpleCacheAdapter(EngineFactory::getInstance(Engines::MEMCACHE, Credentials::getHost(Engines::MEMCACHE)))],
            'Memcached' => [new PsrSimpleCacheAdapter(EngineFactory::getInstance(Engines::MEMCACHED, Credentials::getHost(Engines::MEMCACHED)))],
            'Redis' => [new PsrSimpleCacheAdapter(EngineFactory::getInstance(Engines::REDIS, Credentials::getHost(Engines::REDIS)))],
            'Yac' => [new PsrSimpleCacheAdapter(EngineFactory::getInstance(Engines::YAC, Credentials::getHost(Engines::YAC)))],
            'Apcu' => [new PsrSimpleCacheAdapter(EngineFactory::getInstance(Engines::APCU, ''))]
        ];
        $results = [];
        $limit = 1000;
        foreach ([10, 100, 1000] as $limit) {
            $resultset = [];
            foreach ($origins as $origin => $class) {
                $class = current($class);
                $start = microtime(true);
                $inbounds = array_fill(1, $limit, ["a" => "aa", "b" => "bb", "c" => "cc"]);
                foreach ($inbounds as $i => $inbound) {
                    $class->set("{$origin}.{$i}", $inbound, 10);
                }
                $stop = microtime(true);
                $resultset[$origin] = round(($stop - $start) * 1000, 4);
            }
            $this->assertIsArray($resultset);
            natsort($resultset);
            $results['set_' . $limit] = $resultset;

            $resultget = [];
            foreach ($origins as $origin => $class) {
                $class = current($class);
                $start = microtime(true);
                for ($i = 1; $i <= $limit; $i++) {
                    $class->get("{$origin}.{$i}");
                }
                $stop = microtime(true);
                $resultget[$origin] = round(($stop - $start) * 1000, 4);
            }
            $this->assertIsArray($resultget);
            natsort($resultget);
            $results['get_' . $limit] = $resultget;
            $class->clear();
        }
        // echo print_r(json_encode($results, JSON_PRETTY_PRINT), true);
    }

}