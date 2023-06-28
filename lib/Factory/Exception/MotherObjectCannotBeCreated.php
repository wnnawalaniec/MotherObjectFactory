<?php

declare(strict_types=1);

namespace MotherObjectFactory\Factory\Exception;

class MotherObjectCannotBeCreated extends \LogicException
{
    public static function privateOrProtectedMethodUsedToCreateChildObject(string $method): self
    {
        return new self(sprintf('Cannot use %s to create child object as it\'s not a public', $method));
    }

    public static function nonStaticMethodUsed(string $method): self
    {
        return new self(sprintf('Cannot use %s to create child object as it\'s not a static', $method));
    }

    public static function noWayOfCreatingNewInstanceOfChildClass(): self
    {
        return new self('No public constructor or static method returning new instance of child class found.');
    }

    public static function selfReferencingParameterWithPrivateConstructor(): self
    {
        return new self(
            'One of static factor methods parameters is referencing child class but its constructor is not public,'
            . ' therefor cannot be instanced by mother object'
        );
    }
}