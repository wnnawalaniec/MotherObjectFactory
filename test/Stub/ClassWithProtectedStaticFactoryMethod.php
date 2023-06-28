<?php

declare(strict_types=1);

namespace Tests\MotherObjectFactory\Stub;

class ClassWithProtectedStaticFactoryMethod
{
    private function __construct()
    {
    }

    protected static function create(): self
    {
        return new self();
    }
}