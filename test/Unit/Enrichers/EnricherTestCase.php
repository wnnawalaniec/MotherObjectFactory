<?php

declare(strict_types=1);

namespace Tests\MotherObjectFactory\Unit\Enrichers;

use MotherObjectFactory\Enricher;
use Nette\PhpGenerator\ClassType;
use PHPUnit\Framework\TestCase;

abstract class EnricherTestCase extends TestCase
{
    protected function setupEnrichedObject(Enricher $enricher, string $childClass): void
    {
        $classType = new ClassType($this->motherClassName());
        $enricher->enrich($classType, new \ReflectionClass($childClass));
        eval((string)$classType);
    }

    /**
     * @param array<array{'name': string, 'type': string}> $parameters
     * @throws \ReflectionException
     */
    protected function assertEnrichedObjectHasMethod(
        string $name,
        ?string $returnType,
        bool $static = false,
        array $parameters = []
    ): void {
        $reflection = new \ReflectionClass($this->motherClassName());
        $this->assertTrue($reflection->hasMethod($name));
        $reflectedMethod = $reflection->getMethod($name);
        $this->assertSame($static, $reflectedMethod->isStatic());
        $this->assertSame((string)$returnType, (string)$reflectedMethod->getReturnType());
        $this->assertSame(\count($parameters), $reflectedMethod->getNumberOfParameters());
        foreach ($parameters as ['name' => $name, 'type' => $type]) {
            $found = false;
            foreach ($reflectedMethod->getParameters() as $parameter) {
                if ($parameter->name !== $name) {
                    continue;
                }
                $found = true;
                $this->assertSame($type, (string)$parameter->getType());
            }
            $this->assertTrue($found);
        }
    }

    /**
     * @param array<array{'name': string, 'type': string}> $properties
     * @throws \ReflectionException
     */
    protected function assertEnrichedObjectHasProperties(array $properties): void
    {
        $reflection = new \ReflectionClass($this->motherClassName());
        foreach ($properties as ['name' => $name, 'type' => $type]) {
            $this->assertTrue($reflection->hasProperty($name));
            $this->assertSame((string) $type, (string) $reflection->getProperty($name)->getType());
        }
    }

    protected function assertEnrichedObjectHasNotMethod(string $name): void
    {
        $reflection = new \ReflectionClass($this->motherClassName());
        $this->assertFalse($reflection->hasMethod($name));
    }

    protected function assertEnrichedObjectHasNoProperties(): void
    {
        $this->assertEnrichedObjectHasProperties([]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        static::$testNumber++;
    }

    protected static function motherClassName(): string
    {
        return self::MOTHER_CLASS . static::$testNumber;
    }

    private const MOTHER_CLASS = 'Foo';
    private static int $testNumber = 0;
}