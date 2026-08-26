<?php declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Repositories;

use JuanchoSL\SimpleCache\Repositories\Traits\GenericCountersTrait;
use JuanchoSL\SimpleCache\Repositories\Traits\GenericMultiValuesTrait;
use JuanchoSL\SimpleCache\Repositories\Traits\GenericSingleValueTrait;
use JuanchoSL\SimpleCache\Repositories\Traits\KeysTrait;
use Psr\Log\LogLevel;
use Psr\Log\LoggerAwareTrait;
use JuanchoSL\SimpleCache\Repositories\Traits\TtlTrait;
use JuanchoSL\SimpleCache\Contracts\SimpleCacheInterface;

abstract class AbstractCache implements SimpleCacheInterface
{

    use TtlTrait, KeysTrait, GenericSingleValueTrait, GenericMultiValuesTrait, GenericCountersTrait, LoggerAwareTrait;

    protected bool $debug = false;

    public function setDebug(bool $debug = false): static
    {
        $this->debug = $debug;
        return $this;
    }

    protected function log(\Stringable|string $message, mixed $log_level, array $context = [])
    {
        if (isset($this->logger)) {
            if ($this->debug || $log_level != LogLevel::DEBUG) {
                if ($this->debug) {
                    $context['memory'] = memory_get_usage();
                } elseif (array_key_exists('data', $context)) {
                    unset($context['data']);
                }
                $context['Engine'] = (new \ReflectionClass($this))->getShortName();
                $this->logger->log($log_level, $message, $context);
            }
        }
    }

    public function flush(): bool
    {
        return $this->clear();
    }
}