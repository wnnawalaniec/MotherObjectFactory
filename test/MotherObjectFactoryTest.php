<?php
declare(strict_types=1);

namespace Tests\MotherOfAllObjects;

use MotherOfAllObjects\MotherObjectFactory;
use PHPUnit\Framework\TestCase;
use Tests\MotherOfAllObjects\MotherObjects\SomeClassMother;
use Tests\MotherOfAllObjects\Stub\SomeClass;

class MotherObjectFactoryTest extends TestCase
{
    public function testHasAnyMethod(): void
    {
        $this->assertTrue(method_exists(\SomeClassMother::class, 'create'));
    }

    public function testHasCreateMethod(): void
    {
        $this->assertTrue(method_exists(\SomeClassMother::class, 'any'));

    }

    public function testHasFluentSetters(): void
    {
        foreach ($this->constructorParametersOf(SomeClass::class) as $constructorParameter) {
            $setterMethod = 'with' . ucfirst($constructorParameter->getName());
            $this->assertTrue(method_exists(\SomeClassMother::class, $setterMethod));
            $method = new \ReflectionMethod(\SomeClassMother::class, $setterMethod);
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
            $property = new \ReflectionProperty(\SomeClassMother::class, $constructorParameter->getName());
            $this->assertEquals($constructorParameter->getType(), $property->getType());
            if ($constructorParameter->isDefaultValueAvailable()) {
                $this->assertEquals($constructorParameter->getDefaultValue(), $property->getDefaultValue());
            }
        }
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        eval(MotherObjectFactory::create(SomeClass::class));
    }

    /** @return \ReflectionParameter[] */
    protected function constructorParametersOf(string $class): array
    {
        return (new \ReflectionClass($class))->getConstructor()->getParameters();
    }
}