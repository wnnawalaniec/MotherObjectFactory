<?php

declare(strict_types=1);

namespace Tests\MotherObjectFactory\Unit\Enrichers {

    use MotherObjectFactory\Enrichers\AnyMethod;
    use Tests\MotherObjectFactory\Unit\Enrichers\AnyMethodTest\Stub;

    class AnyMethodTest extends EnricherTestCase
    {
        public function test(): void
        {
            $enricher = new AnyMethod();

            $this->setupEnrichedObject($enricher, Stub::class);

            $this->assertEnrichedObjectHasMethod('any', Stub::class, true);
        }
    }
}

namespace Tests\MotherObjectFactory\Unit\Enrichers\AnyMethodTest {

    class Stub
    {

    }
}