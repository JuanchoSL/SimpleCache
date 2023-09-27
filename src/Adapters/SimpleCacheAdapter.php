<?php
namespace JuanchoSL\SimpleCache\Adapters;

use JuanchoSL\Logger\Debugger;
use JuanchoSL\SimpleCache\Contracts\SimpleCacheInterface;

class SimpleCacheAdapter
{
    private SimpleCacheInterface $cache;

    public function __construct(SimpleCacheInterface $cache)
    {
        $this->cache = $cache;
        Debugger::init(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'logs');
    }

    public function __call($function, $params)
    {
        $time_initial = microtime(true);
        $result = call_user_func_array([$this->cache, $function], $params);
        $time_final = microtime(true) - $time_initial;
        Debugger::info("Time " . get_class($this->cache) . "-{$function}: {$time_final}");
        return $result;
    }
}