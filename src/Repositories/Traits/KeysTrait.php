<?php declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Repositories\Traits;

use JuanchoSL\Exceptions\PreconditionFailedException;
use JuanchoSL\Validators\Types\Iterables\IterableValidations;
use JuanchoSL\Validators\Types\Strings\StringValidation;
use JuanchoSL\Validators\Types\Strings\StringValidations;
use Psr\SimpleCache\InvalidArgumentException;

trait KeysTrait
{

    protected string $chars = 'a-zA-Z0-9_.';
    protected int $max_lenght = 64;
    protected string $extra_chars = '';

    public function getPattern(): string
    {
        return "/^[{$this->chars}{$this->extra_chars}]{1,{$this->max_lenght}}+\$/";//$this->pattern;
    }

    public function setExtraChars(string $extra_chars): void
    {
        if (StringValidation::isValueContainingAny($extra_chars, "{", "}", "(", ")", "/", "\\", "@", ":")) {
            throw new \InvalidArgumentException(sprintf("The chars %s are not allowed for use into keys", $invalids = '{}()/\@:', $this->getPattern()));
        }
        $extra_chars = str_split($extra_chars);
        foreach ($extra_chars as $i => $val) {
            if (in_array($val, ['+', '-'])) {
                $extra_chars[$i] = '\\' . $val;
            }
        }
        $this->extra_chars = implode('', $extra_chars);
        //echo print_r($this->getPattern(), true);
    }

    public function setMaxKeyLenght(int $max_lenght): void
    {
        if ($max_lenght < 64) {
            throw new PreconditionFailedException("The max lenght needs to be 64 or bigger");
        }
        $this->max_lenght = $max_lenght;
    }
    protected function checkKeys(iterable $keys)
    {
        $validation = (new IterableValidations())
            ->isNotEmpty()
            ->isValueValidating((new StringValidations())->isNotEmpty()->isRegex($this->getPattern()));
        if (!$validation($keys)) {
            throw new \InvalidArgumentException(sprintf("Some keys are not valid: %s -> %s", $this->getPattern(), implode('|', $keys)));
        }
        return true;
        foreach ($keys as $key) {
            $this->checkKey($key);
        }
    }
    protected function checkKey(string $key)
    {
        return $this->checkKeys([$key]);

        if (!(new StringValidations())->isNotEmpty()->isRegex($this->getPattern())->getResult($key)) {
            throw new \InvalidArgumentException("The key '{$key}' is not valid");
        }
        return true;
    }

}