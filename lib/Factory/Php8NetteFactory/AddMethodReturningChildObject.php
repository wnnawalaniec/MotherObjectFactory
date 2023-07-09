<?php

declare(strict_types=1);

namespace MotherObjectFactory\Factory\Php8NetteFactory;

use MotherObjectFactory\Factory\Exception\MotherObjectCannotBeCreated;
use MotherObjectFactory\Tools\ReflectionUtils;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\Parameter;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class AddMethodReturningChildObject
{
    /**
     * @throws ReflectionException
     */
    public function __invoke(
        ClassType $motherObject,
        ReflectionClass $child,
        string $methodName = 'create'
    ): ClassType {
        $methodCreatingChildObject = $this->findMethodWhichCanBeUsedToCreteNewInstance($child);
        $factoryMethod = $motherObject->addMethod($methodName);
        $factoryMethod->setReturnType($this->globalNamespaceClassName($child));
        $this->setMethodBody($factoryMethod, $methodCreatingChildObject, $child);

        return $motherObject;
    }

    private function globalNamespaceClassName(ReflectionClass $child): string
    {
        return '\\' . $child->getName();
    }

    private function setMethodBody(
        Method $factoryMethod,
        ?ReflectionMethod $childFactoryMethod,
        ReflectionClass $child
    ): void {
        if ($childFactoryMethod === null) {
            $factoryMethod->setBody(
                $this->createCodeUsingConstructor($this->globalNamespaceClassName($child), '')
            );
            return;
        }

        $parameters = [];
        foreach ($childFactoryMethod->getParameters() as $childParameter) {
            $motherParameter = $factoryMethod->addParameter($childParameter->name);
            $motherParameter->setType(ReflectionUtils::typeToString($childParameter->getType()));
            if ($childParameter->isDefaultValueAvailable()) {
                if ($child->getConstructor()?->isPublic()) {
                    $motherParameter->setDefaultValue($childParameter->getDefaultValue());
                } else {
                    continue;
                }
            }

            $parameters[] = $motherParameter;
        }
        $parametersClass = implode(', ', array_map(fn(Parameter $p) => '$' . $p->getName(), $parameters));
        if ($childFactoryMethod->isConstructor()) {
            $factoryMethod->setBody(
                $this->createCodeUsingConstructor($this->globalNamespaceClassName($child), $parametersClass)
            );
        } else {
            $factoryMethod->setBody(
                $this->createCodeUsingStaticMethod(
                    $this->globalNamespaceClassName($child),
                    $childFactoryMethod->name,
                    $parametersClass
                )
            );
        }
    }

    private function createCodeUsingConstructor(string $classReference, string $parameters): string
    {
        return sprintf(
            "return new %s(%s);",
            $classReference,
            $parameters
        );
    }

    private function createCodeUsingStaticMethod(
        string $classReference,
        string $staticMethod,
        string $parameters
    ): string {
        return sprintf(
            "return %s::%s(%s);",
            $classReference,
            $staticMethod,
            $parameters
        );
    }

    /**
     * @return ReflectionMethod|null Returns instance of public constructor or static method creating instance of
     * given reflected class. Null is returned if default empty constructor should be used.
     * @throws MotherObjectCannotBeCreated when there is no way to create instance of a class
     */
    protected function findMethodWhichCanBeUsedToCreteNewInstance(ReflectionClass $child): ?ReflectionMethod
    {
        // If there is public constructor use it
        if ($child->getConstructor() !== null && $child->getConstructor()->isPublic()) {
            return $child->getConstructor();
        }

        // If there is no public constructor, search for static method returning instance of child class
        foreach ($child->getMethods(ReflectionMethod::IS_STATIC) as $staticMethod) {
            if (!$staticMethod->isPublic()) {
                continue;
            }

            /*
             * Skip method if there is no return type, as we don't want to parse whole method body to look for creating
             * new instance.
             */
            if (!$staticMethod->hasReturnType()) {
                continue;
            }

            $isOfReflectedClassType = static fn(\ReflectionType $type, \ReflectionClass $class) => in_array(
                $type->getName(),
                [$class->getName(), 'self', 'static']
            );

            /*
             * If we are using static method this means that there is no public constructor. Therefor we must
             * search all parameters used in that static method if there is no self referencing parametr, as we won't
             * be able to that method with such parameter in that case.
             * E.g. assume we have method like:
             * ```
             * public function create(self $other): self {...}
             * ```
             * If there is no public constructor or other static method not requiring object of that class as one of
             * parameters, there is no way to use that method.
             * Only case when it's possible to use it is when default value is given:
             * ```
             * public function create(self $other = new self()): self {...}
             * ```
             * As it's legal to use `new` as default value form PHP 8.1.0.
             */
            foreach ($staticMethod->getParameters() as $parameter) {
                if ($parameter->getType() === null) {
                    continue;
                }

                if ($parameter->getType() instanceof \ReflectionUnionType) {
                    // TODO each type must be checked separately
                    continue;
                }

                if (!$isOfReflectedClassType($parameter->getType(), $child)) {
                    continue;
                }

                if (!$parameter->isDefaultValueAvailable() && !$child->getConstructor()->isPublic()) {
                    throw MotherObjectCannotBeCreated::selfReferencingParameterWithPrivateConstructor();
                }
            }

            if ($staticMethod->getReturnType() instanceof \ReflectionUnionType) {
                foreach ($staticMethod->getReturnType()->getTypes() as $reflectionType) {
                    if ($isOfReflectedClassType($reflectionType, $child)) {
                        return $staticMethod;
                    }
                }
            } elseif ($isOfReflectedClassType($staticMethod->getReturnType(), $child)) {
                return $staticMethod;
            }
        }

        // If there was constructor but no public throw exception, as we cannot use `new`
        if ($child->getConstructor() !== null && !$child->getConstructor()->isPublic()) {
            throw MotherObjectCannotBeCreated::noWayOfCreatingNewInstanceOfChildClass();
        }

        // there is no public static method and no constructor at all, so we can use default empty constructor
        return null;
    }
}