<?php

declare(strict_types=1);

namespace Tests\MotherObjectFactory\Unit\Factory\Php8NetteFactory;

use MotherObjectFactory\Factory\Exception\MotherObjectCannotBeCreated;
use MotherObjectFactory\Factory\Php8NetteFactory\AddMethodReturningChildObject;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Parameter;
use Tests\MotherObjectFactory\Stub\BaseClass;
use Tests\MotherObjectFactory\Stub\ClassWithPrivateConstructor;
use Tests\MotherObjectFactory\Stub\ClassWithPrivateStaticFactoryMethod;
use Tests\MotherObjectFactory\Stub\ClassWithProtectedConstructor;
use Tests\MotherObjectFactory\Stub\ClassWithProtectedStaticFactoryMethod;
use Tests\MotherObjectFactory\Stub\ClassWithPublicConstructor;
use Tests\MotherObjectFactory\Stub\ClassWithStaticFactoryMethodWithoutParameters;
use Tests\MotherObjectFactory\Stub\ClassWithStaticFactoryMethodWithParameters;
use Tests\MotherObjectFactory\Support\Assertion\ExceptionAssertions;
use Tests\MotherObjectFactory\Unit\FeaturesTest;

class AddMethodReturningChildObjectTest extends FeaturesTest
{
    use ExceptionAssertions;

    public function test_ChildHasOnlyPrivateConstructor_ThrowsException(): void
    {
        $mother = $this->mother();
        $childClass = $this->class(ClassWithPrivateConstructor::class);

        $act = fn() => (new AddMethodReturningChildObject())(
            motherObject: $mother,
            child: $childClass
        );

        $expectedException = MotherObjectCannotBeCreated::noWayOfCreatingNewInstanceOfChildClass();
        $this->assertExceptionObject($expectedException, $act);
    }

    public function test_ChildHasOnlyProtectedConstructor_ThrowsException(): void
    {
        $mother = $this->mother();
        $childClass = $this->class(ClassWithProtectedConstructor::class);

        $act = fn() => (new AddMethodReturningChildObject())(
            motherObject: $mother,
            child: $childClass
        );

        $expectedException = MotherObjectCannotBeCreated::noWayOfCreatingNewInstanceOfChildClass();
        $this->assertExceptionObject($expectedException, $act);
    }

    public function test_ChildOnlyPrivateConstructorAndStaticFactoryMethod_ThrowsException(): void
    {
        $mother = $this->mother();
        $childClass = $this->class(ClassWithPrivateStaticFactoryMethod::class);

        $act = fn() => (new AddMethodReturningChildObject())(
            motherObject: $mother,
            child: $childClass
        );

        $expectedException = MotherObjectCannotBeCreated::noWayOfCreatingNewInstanceOfChildClass();
        $this->assertExceptionObject($expectedException, $act);
    }

    public function test_ChildOnlyProtectedConstructorAndStaticFactoryMethod_ThrowsException(): void
    {
        $mother = $this->mother();
        $childClass = $this->class(ClassWithProtectedStaticFactoryMethod::class);

        $act = fn() => (new AddMethodReturningChildObject())(
            motherObject: $mother,
            child: $childClass
        );

        $expectedException = MotherObjectCannotBeCreated::noWayOfCreatingNewInstanceOfChildClass();
        $this->assertExceptionObject($expectedException, $act);
    }

    public function test_UsingPublicStaticFactoryMethod_AddsMethod(): void
    {
        $mother = $this->mother();
        $expectedMethodName = 'creatChild';
        $expectedChildClass = ClassWithStaticFactoryMethodWithoutParameters::class;
        $childClassReflection = $this->class($expectedChildClass);
        $expectedReturnType = sprintf('\\%s', $childClassReflection->getName());
        $expectedMethodBody = sprintf(
            'return \\%s::%s();',
            $expectedChildClass,
            'create'
        );

        (new AddMethodReturningChildObject())(
            motherObject: $mother,
            child: $childClassReflection,
            methodName: $expectedMethodName
        );

        $this->assertTrue($mother->hasMethod($expectedMethodName));
        $this->assertSame($expectedReturnType, $mother->getMethod($expectedMethodName)->getReturnType());
        $this->assertSame($expectedMethodBody, $mother->getMethod($expectedMethodName)->getBody());
    }

    public function test_UsingPublicConstructor_AddsMethod(): void
    {
        $mother = $this->mother();
        $expectedChildClass = ClassWithPublicConstructor::class;
        $childClassReflection = $this->class($expectedChildClass);
        $expectedMethodName = 'creatChild';
        $expectedReturnType = sprintf('\\%s', $childClassReflection->getName());
        $expectedMethodBody = sprintf(
            'return new \\%s();',
            $expectedChildClass
        );

        (new AddMethodReturningChildObject())(
            motherObject: $mother,
            child: $childClassReflection,
            methodName: $expectedMethodName
        );


        $this->assertTrue($mother->hasMethod($expectedMethodName));
        $this->assertSame($expectedReturnType, $mother->getMethod($expectedMethodName)->getReturnType());
        $this->assertSame($expectedMethodBody, $mother->getMethod($expectedMethodName)->getBody());
        $this->assertSame($expectedMethodBody, $mother->getMethod($expectedMethodName)->getBody());
    }

    public function test_MethodWithParameters_AddsMethod(): void
    {
        $mother = $this->mother();
        $childStaticFactoryMethod = 'createWithParams';
        $expectedChildClass = ClassWithStaticFactoryMethodWithParameters::class;
        $childClassReflection = $this->class($expectedChildClass);
        $expectedMethodName = 'creatChild';
        $expectedParameters = [
            (new Parameter('notTyped')),
            (new Parameter('typed'))->setType('string'),
            (new Parameter('nullable'))->setType('int')->setNullable(),
            (new Parameter('mixed'))->setType('object|int'),
            (new Parameter('aliasType'))->setType('\\' . BaseClass::class),
            (new Parameter('fullClassName'))->setType('\\' . BaseClass::class)
        ];
        $expectedReturnType = sprintf('\\%s', $childClassReflection->getName());
        $expectedMethodBody = sprintf(
            'return \\%s::%s($notTyped, $typed, $nullable, $mixed, $aliasType, $fullClassName);',
            $expectedChildClass,
            $childStaticFactoryMethod
        );

        (new AddMethodReturningChildObject())(
            motherObject: $mother,
            child: $childClassReflection,
            methodName: $expectedMethodName
        );

        $this->assertTrue($mother->hasMethod($expectedMethodName));
        $this->assertSame($expectedReturnType, $mother->getMethod($expectedMethodName)->getReturnType());
        $this->assertSame($expectedMethodBody, $mother->getMethod($expectedMethodName)->getBody());
        $this->assertEquals(
            $expectedParameters,
            array_values($mother->getMethod($expectedMethodName)->getParameters())
        );
    }

    protected function mother(): ClassType
    {
        return new ClassType('Mother');
    }

    protected function class(object|string $name): \ReflectionClass
    {
        return new \ReflectionClass($name);
    }
}