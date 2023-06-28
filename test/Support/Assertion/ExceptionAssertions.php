<?php

declare(strict_types=1);

namespace Tests\MotherObjectFactory\Support\Assertion;

use PHPUnit\Framework\TestCase;

trait ExceptionAssertions
{
    public function assertExceptionObject(\Exception $exception, callable $act): void
    {
        $this->expectExceptionObject($exception);
        $act();
    }
}