<?php

declare(strict_types=1);

namespace Tests\MotherObjectFactory\Stub;

use Tests\MotherObjectFactory\Stub\BaseClass as alias;

class ClassWithStaticFactoryMethodWithParameters
{
    private function __construct()
    {
    }

    public static function createWithParams(
        $notTyped,
        string $typed,
        ?int $nullable,
        object|int $mixed,
        alias $aliasType,
        \Tests\MotherObjectFactory\Stub\BaseClass $fullClassName,
        self $default = new self()
    ): self {
        return new self();
    }
}