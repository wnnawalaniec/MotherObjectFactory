<?php

declare(strict_types=1);

namespace Tests\MotherObjectFactory\Stub;

class ClassWithNonStaticFactoryMethod
{
    private function __construct()
    {
    }

    public function create(): self
    {
        return new self();
    }
}