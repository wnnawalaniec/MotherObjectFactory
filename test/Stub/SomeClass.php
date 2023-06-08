<?php

declare(strict_types=1);

namespace Tests\MotherObjectFactory\Stub;

final class SomeClass
{
    public function __construct(
        string $s,
        ?int $i = null,
        float $f = 1.1,
        int|float $if = 1,
        array $arr = [],
        object $obj = new \stdClass()
    ) {
        $this->foo = $s;
        $this->i = $i;
        $this->f = $f;
        $this->if = $if;
        $this->arr = $arr;
        $this->obj = $obj;
    }

    public function foo(): string
    {
        return $this->foo;
    }

    private string $foo;
    private ?int $i;
    private float $f;
    private int|float $if;
    private array $arr;
    private object $obj;
}