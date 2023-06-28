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
}