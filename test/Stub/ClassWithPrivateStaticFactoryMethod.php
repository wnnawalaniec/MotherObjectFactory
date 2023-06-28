<?php

declare(strict_types=1);

namespace Tests\MotherObjectFactory\Stub;

class ClassWithPrivateStaticFactoryMethod
{
    private function __construct()
    {
    }

    private static function create(): self
    {
        return new self();
    }
}