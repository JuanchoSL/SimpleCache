<?php declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Repositories;

use JuanchoSL\Exceptions\DestinationUnreachableException;
use JuanchoSL\Validators\Types\Entities\EntityValidations;
use JuanchoSL\Validators\Types\Integers\IntegerValidation;
use JuanchoSL\Validators\Types\Integers\IntegerValidations;
use JuanchoSL\Validators\Types\Strings\StringValidations;
use Psr\Log\LogLevel;

class FileCache extends AbstractCache
{

    protected string $cache_dir;

    public function __construct(string $host)
    {
        $this->cache_dir = rtrim($host, DIRECTORY_SEPARATOR);
        if (!file_exists($this->cache_dir)) {
            if (!mkdir($this->cache_dir, 0777, true)) {
                $exception = new DestinationUnreachableException("Can not connect to the required destiny");
                $this->log($exception, LogLevel::ERROR, [
                    'exception' => $exception,
                    'credentials' => [
                        'host' => $this->cache_dir
                    ]
                ]);
                throw $exception;
            }
        }
    }

    public function getHost(): string
    {
        return $this->cache_dir;
    }

    /**
     * @return array<int, string> $array
     */
    public function getAllKeys(): array
    {
        $response = [];
        $files = glob($this->cache_dir . "/*");
        if (!empty($files)) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    $response[] = basename($file);
                }
            }
        }
        return $response;
    }

    public function clear(): bool
    {
        $result = true;
        foreach ($this->getAllKeys() as $key) {
            $result = ($this->delete($key)) ? $result : false;
        }
        $this->log("Cleared cache {prefix}", LogLevel::DEBUG, ['prefix' => $this->getHost(), 'method' => __FUNCTION__, 'result' => intval($result)]);
        return $result;
    }

    public function has(string $key): bool
    {
        $meta = $this->getMetadata($key);
        if (is_array($meta) && array_key_exists('ttl', $meta) && is_numeric($meta['ttl'])) {
            return $meta['ttl'] > time();
        }
        return false;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $data = $this->getMetadata($key);
        $validations = (new EntityValidations())
            ->isKeyContaining('ttl')
            ->isKeyContaining('data')
            ->isValueAttributeValidating('ttl', (new IntegerValidations())->isValueGreatherThan(time()))
        ;
        if ($validations($data)) {
            $data_value_validation = (new StringValidations)->is()->isNotEmpty()->isSerialized();
            if ($data_value_validation($data['data'])) {
                $data['data'] = unserialize($data['data']);
            }
            return $data['data'];
        }
        return (is_callable($default)) ? $default() : $default;
    }

    public function set(string $key, mixed $value, \DateInterval|null|int $ttl = null): bool
    {
        $ttl = $this->maxTtl($ttl);
        if ($ttl > 0) {
            $this->checkKey($key);
            if (is_object($value) || is_array($value)) {
                $value = serialize($value);
            }
            $value = ['ttl' => time() + $ttl, 'data' => $value];
            $result = file_put_contents($this->cache_dir . DIRECTORY_SEPARATOR . $key, serialize($value), LOCK_EX);
            $this->log("The key {key} is going to save", LogLevel::INFO, ['key' => $key, 'data' => $value, 'method' => __FUNCTION__, 'result' => intval($result)]);
            return $result !== false;
        } else {
            return $this->delete($key);
        }
    }

    public function delete(string $key): bool
    {
        $this->checkKey($key);
        $cache_file = $this->cache_dir . DIRECTORY_SEPARATOR . $key;
        $result = (file_exists($cache_file)) ? unlink($cache_file) : false;
        $this->log("The file {key} is going to delete", LogLevel::INFO, ['key' => $cache_file, 'method' => __FUNCTION__, 'result' => intval($result)]);
        return $result;
    }

    public function replace(string $key, mixed $value): bool
    {
        $this->checkKey($key);
        $data = $this->getMetadata($key);
        if ($data !== false) {
            $result = $this->set($key, $value, $data['ttl'] - time());
            $this->log("The key {key} is going to be replaced", LogLevel::INFO, ['key' => $key, 'data' => ['old' => $data['data'], 'new' => $value], 'method' => __FUNCTION__, 'result' => intval($result)]);
            return $result;
        }
        return false;
    }

    /**
     * @return array{ttl: int, data: mixed}|false
     */
    protected function getMetadata(string $key): array|bool
    {
        $this->checkKey($key);
        $cache_file = $this->cache_dir . DIRECTORY_SEPARATOR . $key;
        if (file_exists($cache_file)) {
            $data = file_get_contents($cache_file);
            if (!empty($data)) {
                $data = (array) unserialize($data);
                if (is_array($data) && array_key_exists('ttl', $data) && IntegerValidation::isValueGreatherThan($data['ttl'], time())) {
                    return $data;
                }
            }
        }
        $this->log("The file {cache_file} does not exists", LogLevel::INFO, ['cache_file' => $cache_file, 'method' => __FUNCTION__]);
        return false;
    }

}