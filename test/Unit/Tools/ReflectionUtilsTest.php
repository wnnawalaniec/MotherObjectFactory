<?php

declare(strict_types=1);

namespace Tests\MotherObjectFactory\Unit\Tools;

use MotherObjectFactory\Tools\ReflectionUtils;
use PHPUnit\Framework\TestCase;
use Tests\MotherObjectFactory\Stub\ClassForStaticFactoryMethodFindingTests;

class ReflectionUtilsTest extends TestCase
{
    public function testFactoryMethods(): void
    {
        $this->assertSame(
            [
                '__construct',
                'returnBaseClassType',
                'returnSelfType',
                'returnStaticType',
                'returnUnionType',
                'returnUnionTypeWithBaseSafeAndStatic',
                'returnBaseClassAsAlias',
                'returnSelfClassByFullName',
                'returnSelClassByShortName',
                'returnInterfaceType',
                'returnIntersectType',
            ],
            ReflectionUtils::allMethodsAllowingToCreateNewInstanceFromOutsideClassScope(
                new \ReflectionClass(ClassForStaticFactoryMethodFindingTests::class)
            )
        );
    }

    public function testTypeToString(): void
    {
        $parameterWithNoType = $this->firstParameter(fn ($x) => $x);
        $this->assertSame('', ReflectionUtils::typeToString($parameterWithNoType));

        $parameterIntType = $this->firstParameter(fn (int $x) => $x);
        $this->assertSame('int', ReflectionUtils::typeToString($parameterIntType));

        $parameterWithBuiltInClassType = $this->firstParameter(fn (\Iterator $x) => $x);
        $this->assertSame('\Iterator', ReflectionUtils::typeToString($parameterWithBuiltInClassType));

        $parameterWithNullableType = $this->firstParameter(fn (?int $x) => $x);
        $this->assertSame('?int', ReflectionUtils::typeToString($parameterWithNullableType));

        $parameterWithUnionType = $this->firstParameter(fn (int|bool $x) => $x);
        $this->assertSame('int|bool', ReflectionUtils::typeToString($parameterWithUnionType));

        $parameterWithUnionHavingObjects = $this->firstParameter(fn (int|\Iterator $x) => $x);
        $this->assertSame('\Iterator|int', ReflectionUtils::typeToString($parameterWithUnionHavingObjects));

        $parameterWithUnionHavingObjects = $this->firstParameter(fn (\Iterator|int $x) => $x);
        $this->assertSame('\Iterator|int', ReflectionUtils::typeToString($parameterWithUnionHavingObjects));

        $parameterWithIntersectionType = $this->firstParameter(fn (\Iterator&\Countable $x) => $x);
        $this->assertSame('\Iterator&\Countable', ReflectionUtils::typeToString($parameterWithIntersectionType));
    }

    private function firstParameter(callable $function): ?\ReflectionType
    {
        return (new \ReflectionFunction($function))->getParameters()[0]->getType();
    }
}