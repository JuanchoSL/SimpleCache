<?php declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Repositories\Traits;

use Psr\Log\LogLevel;

trait GenericMultiValuesTrait
{
    /**
     * @param iterable<string, mixed> $values
     */
    public function setMultiple(iterable $values, \DateInterval|null|int $ttl = null): bool
    {
        $result = true;
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $this->maxTtl($ttl))) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * @param iterable<int, string> $keys
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $response = [];
        $default_value = $default;
        foreach ($keys as $i => $key) {
            if (is_iterable($default)) {
                $default_value = (array_key_exists($key, $default)) ? $default[$key] : $default[$i];
            }
            if (!is_null($value = $this->get($key, $default_value))) {
                $response[$key] = $value;
            }
            //$response[$key] = $this->get($key, $default_value) ?? $default_value;
        }
        return $response;
    }
    /**
     * @param iterable<int, string> $keys
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $fails = 0;
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $fails++;
            }
        }
        $counter = count($keys) - $fails;
        $this->log("Some keys are going to be deleted", LogLevel::DEBUG, ['keys' => $keys, 'method' => __FUNCTION__, 'result' => ['ok' => $counter, 'ko' => $fails]]);
        return ($fails === 0);
    }
}