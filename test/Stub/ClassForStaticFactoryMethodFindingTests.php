<?php

declare(strict_types=1);

namespace Tests\MotherObjectFactory\Stub;

use Tests\MotherObjectFactory\Stub\BaseClass as alias;

class ClassForStaticFactoryMethodFindingTests extends BaseClass implements BaseInterface
{
    public function __construct()
    {
    }

    public static function returnBaseClassType(): BaseClass
    {
        return new self();
    }

    public static function returnSelfType(): self
    {
        return new self();
    }

    public static function returnStaticType(): static
    {
        return new self();
    }

    public static function returnUnionType(): BaseClass|int
    {
        return new self();
    }

    public static function returnUnionTypeWithBaseSafeAndStatic(): BaseClass|self|static
    {
        return new self();
    }

    public static function returnBaseClassAsAlias(): alias
    {
        return new self();
    }

    public static function returnSelfClassByFullName(): \Tests\MotherObjectFactory\Stub\ClassForStaticFactoryMethodFindingTests
    {
        return new self();
    }

    public static function returnSelClassByShortName(): ClassForStaticFactoryMethodFindingTests
    {
        return new self();
    }

    public static function returnInterfaceType(): BaseInterface
    {
        return new self();
    }

    public static function returnIntersectType(): BaseClass&BaseInterface
    {
        return new self();
    }

    public static function returnNoType(): void
    {

    }

    public static function returnObjectType(): object
    {
        return new self();
    }

    public static function returnMixedType(): mixed
    {
        return new self();
    }

    public function nonStatic(): self
    {
        return new self();
    }
}