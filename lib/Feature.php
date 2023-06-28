<?php

declare(strict_types=1);

namespace MotherObjectFactory;

/**
 * It represents features which created mother object should have.
 */
enum Feature: string
{
    /**
     * It should have methods like `withFoo(...` and `build()` to add make possible multistep creating of object.
     */
    case BUILDER_PATTERN = 'BUILDER_PATTERN';

    /**
     * It should have method like `any(): Foo` to simply create default object
     */
    case STATIC_FACTORY_METHOD_PATTERN = 'STATIC_FACTORY_METHOD_PATTERN';
}