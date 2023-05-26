<?php
declare(strict_types=1);

namespace Tests\MotherOfAllObjects\Stub;

final class SomeClass
{
    public function __construct(string $s, ?int $i= null, float $f = 1.1, int|float $if = 1)
    {
        $this->foo = $s;
        $this->i = $i;
        $this->f = $f;
        $this->if = $if;
    }

    public function foo(): string
    {
        return $this->foo;
    }

    private string $foo;
    private ?int $i;
    private float $f;
    private int|float $if;
}