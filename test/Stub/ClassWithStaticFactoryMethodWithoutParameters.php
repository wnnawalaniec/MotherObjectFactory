<?php

declare(strict_types=1);

namespace Tests\MotherObjectFactory\Stub;

class ClassWithStaticFactoryMethodWithoutParameters
{
    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }
}