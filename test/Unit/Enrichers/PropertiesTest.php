<?php

declare(strict_types=1);

namespace Tests\MotherObjectFactory\Unit\Enrichers {

    use MotherObjectFactory\Enrichers\Properties;
    use Tests\MotherObjectFactory\Unit\Enrichers\PropertiesTest\WithConstructor;
    use Tests\MotherObjectFactory\Unit\Enrichers\PropertiesTest\WithoutConstructor;

    class PropertiesTest extends EnricherTestCase
    {
        public function testClassWithConstructor(): void
        {
            $enricher = new Properties();

            $this->setupEnrichedObject(enricher: $enricher, childClass: WithConstructor::class);

            $expectedProperties = [
                ['name' => 's', 'type' => 'string'],
                ['name' => 'i', 'type' => '?int'],
                ['name' => 'f', 'type' => 'float'],
                ['name' => 'if', 'type' => 'int|float'],
                ['name' => 'any', 'type' => ''],
            ];
            $this->assertEnrichedObjectHasMethod(
                name: '__construct',
                returnType: null,
                parameters: $expectedProperties
            );
            $this->assertEnrichedObjectHasProperties($expectedProperties);
        }

        public function testClassWithoutConstructor(): void
        {
            $enricher = new Properties();

            $this->setupEnrichedObject(enricher: $enricher, childClass: WithoutConstructor::class);

            $this->assertEnrichedObjectHasNotMethod(name: '__construct');
            $this->assertEnrichedObjectHasNoProperties();
        }
    }
}

namespace Tests\MotherObjectFactory\Unit\Enrichers\PropertiesTest {

    final class WithConstructor
    {
        public function __construct(
            private string $s,
            private ?int $i = null,
            private float $f = 1.1,
            private int|float $if = 1,
            private $any
        ) {
        }
    }

    final class WithoutConstructor
    {
    }
}