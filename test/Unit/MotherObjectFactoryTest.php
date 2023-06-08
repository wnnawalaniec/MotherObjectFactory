<?php

declare(strict_types=1);

namespace Tests\MotherObjectFactory\Unit;

use MotherObjectFactory\MotherObjectFactory;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Tests\MotherObjectFactory\Stub\SomeClass;

class MotherObjectFactoryTest extends TestCase
{
    public function testHasAnyMethod(): void
    {
        $this->assertTrue(method_exists(\SomeClassMother::class, 'any'));
    }

    public function testHasBuildMethod(): void
    {
        $this->assertTrue(method_exists(\SomeClassMother::class, 'build'));
    }

    public function testHasFluentSetters(): void
    {
        foreach ($this->constructorParametersOf(SomeClass::class) as $constructorParameter) {
            $setterMethod = 'with' . ucfirst($constructorParameter->getName());
            $this->assertTrue(method_exists(\SomeClassMother::class, $setterMethod));
            $method = new ReflectionMethod(\SomeClassMother::class, $setterMethod);
            $this->assertEquals(
                'self',
                (string)$method->getReturnType()
            );
        }
    }

    function testHasPropertiesRequiredToConstructChildClass(): void
    {
        foreach ($this->constructorParametersOf(SomeClass::class) as $constructorParameter) {
            $this->assertTrue(property_exists(\SomeClassMother::class, $constructorParameter->getName()));
            $property = new ReflectionProperty(\SomeClassMother::class, $constructorParameter->getName());
            $this->assertEquals($constructorParameter->getType(), $property->getType());
        }
    }

    public function testInstance(): void
    {
        $factory = MotherObjectFactory::instance();

        $this->assertInstanceOf(MotherObjectFactory::class, $factory);
    }

    public function testInstanceReturnsSameInstance(): void
    {
        $factory1 = MotherObjectFactory::instance();
        $factory2 = MotherObjectFactory::instance();

        $this->assertSame($factory1, $factory2);
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        eval(MotherObjectFactory::instance()->create(SomeClass::class));
    }

    /**
     * @return ReflectionParameter[]
     * @throws ReflectionException
     */
    protected function constructorParametersOf(string $class): array
    {
        return (new ReflectionClass($class))->getConstructor()->getParameters();
    }
}