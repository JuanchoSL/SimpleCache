<?php

declare(strict_types=1);

namespace JuanchoSL\SimpleCache\Repositories;

trait SerializeTrait
{

    public function isSerialized(string $value): bool
    {
        return !empty(preg_match('/^([C|O|a|i|s]+):\d+(:("\w+":\d+:)?([\\\s\w\d:"{};*.]+))?/', $value));
    }

}