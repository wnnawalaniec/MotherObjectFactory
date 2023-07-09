<?php

declare(strict_types=1);

namespace MotherObjectFactory\Tools;

use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;

final class ReflectionUtils
{
    /** @return string[] methods names */
    public static function allMethodsAllowingToCreateNewInstanceFromOutsideClassScope(ReflectionClass $class): array
    {
        $methods = [];
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->isConstructor()) {
                $methods[] = $method->getName();
                continue;
            }

            if (!$method->hasReturnType() || !$method->isStatic()) {
                continue;
            }

            $returnType = $method->getReturnType();
            if (
                $returnType instanceof ReflectionUnionType
                || $returnType instanceof ReflectionIntersectionType
            ) {
                foreach ($returnType->getTypes() as $type) {
                    if (self::isSubTypeOfClass($type, $class)) {
                        $methods[] = $method->getName();
                        break;
                    }
                }
                continue;
            }

            if (self::isSubTypeOfClass($returnType, $class)) {
                $methods[] = $method->getName();
            }
        }

        return $methods;
    }

    protected static function isSubTypeOfClass(ReflectionNamedType $returnType, ReflectionClass $class): bool
    {
        return !$returnType->isBuiltin()
            && (
                in_array($returnType, ['self', 'static', $class->getName()])
                || $class->isSubclassOf($returnType->getName())
            );
    }

    public static function typeToString(?\ReflectionType $type): string
    {
        if ($type === null) {
            return '';
        }

        if ($type instanceof ReflectionNamedType) {
            if ($type->isBuiltin()) return (string) $type;
            return '\\' . $type;
        }

        if ($type instanceof ReflectionUnionType) {
            return implode('|', array_map(['self', 'typeToString'], $type->getTypes()));
        }

        if ($type instanceof ReflectionIntersectionType) {
            return implode('&', array_map(['self', 'typeToString'], $type->getTypes()));
        }

        return (string) $type;
    }

    public static function isScalar(string $type): bool
    {
        return in_array($type, ['int', 'float', 'string', 'bool'], true);
    }
}