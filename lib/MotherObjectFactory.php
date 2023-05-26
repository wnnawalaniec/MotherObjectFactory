<?php
declare(strict_types=1);

namespace MotherOfAllObjects;

use Nette\PhpGenerator\ClassType;

final class MotherObjectFactory
{
    private const MOTHER_OBJECT_FACTORY_METHOD_NAME = 'newObject';
    private const DEFAULT_CHILD_FACTORY_METHOD_NAME = 'any';
    private const CHILD_CLASS_FACTORY_METHOD_NAME = 'create';

    public static function create(string $class): string
    {
        $childReflection = new \ReflectionClass($class);
        $motherClass = new ClassType("{$childReflection->getShortName()}Mother");
        $motherClass->setFinal();
        self::addBuilderProperties($motherClass, $childReflection);
        self::addFluentBuilderMethods($motherClass, $childReflection);
        self::addDefaultTargetFactoryMethod($motherClass, $childReflection);
        self::addTargetFactoryMethod($motherClass, $childReflection);
        self::addPrivateConstructor($motherClass, $childReflection);
        return (string)$motherClass;
    }

    private static function addPrivateConstructor(ClassType $motherObject, \ReflectionClass $child): void
    {
        $construct = $motherObject->addMethod('__construct');
        $construct->setPrivate();
        $childConstructorParameters = $child->getConstructor()->getParameters();
        foreach ($childConstructorParameters as $constructorParameter) {
            $construct->addBody("\$this->{$constructorParameter->getName()}=\${$constructorParameter->getName()};");
            $parameter = $construct->addParameter($constructorParameter->getName());
            $parameter->setNullable($constructorParameter->allowsNull());
            $parameter->setType($constructorParameter->hasType() ? (string)$constructorParameter->getType() : null);
            if ($constructorParameter->isDefaultValueAvailable()) {
                $parameter->setDefaultValue($constructorParameter->getDefaultValue());
            }
        }
    }

    private static function addDefaultTargetFactoryMethod(ClassType $motherObject, \ReflectionClass $child): void
    {
        $any = $motherObject->addMethod(self::DEFAULT_CHILD_FACTORY_METHOD_NAME);
        $any->setReturnType($child->getName());
        $any->setStatic();
        $any->addBody(
            sprintf(
                'return self::%s()->%s();',
                self::MOTHER_OBJECT_FACTORY_METHOD_NAME,
                self::CHILD_CLASS_FACTORY_METHOD_NAME
            )
        );
    }

    private static function addTargetFactoryMethod(ClassType $motherObject, \ReflectionClass $child): void
    {
        $create = $motherObject->addMethod(self::CHILD_CLASS_FACTORY_METHOD_NAME);
        $create->setReturnType($child->getName());
        $propertyCalls = array_map(
            fn(\ReflectionParameter $parameter) => "\$this->{$parameter->getName()}",
            $child->getConstructor()->getParameters()
        );
        $create->setBody(
            sprintf(
                "return new %s(%s);",
                $child->getName(),
                implode(',', $propertyCalls)
            )
        );
    }

    private static function addBuilderProperties(ClassType $motherObject, \ReflectionClass $child): void
    {
        foreach ($child->getConstructor()->getParameters() as $parameter) {
            $property = $motherObject->addProperty($parameter->name)
                ->setType($parameter->hasType() ? (string)$parameter->getType() : null);
            if ($parameter->isDefaultValueAvailable()) {
                $property->setValue($parameter->getDefaultValue());
            }
        }
    }

    private static function addFluentBuilderMethods(ClassType $motherObject, \ReflectionClass $child): void
    {
        foreach ($child->getConstructor()->getParameters() as $parameter) {
            $setter = $motherObject->addMethod('with' . ucfirst($parameter->getName()));
            $setter->addParameter($parameter->getName())
                ->setType($parameter->hasType() ? (string)$parameter->getType() : null);
            $setter->setReturnType('self');
            $setter->setBody("\$this->{$parameter->getName()}=\${$parameter->getName()}; return \$this;");
        }
    }
}