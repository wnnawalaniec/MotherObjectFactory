<?php

declare(strict_types=1);

namespace MotherObjectFactory\Factory;

use MotherObjectFactory\Factory;
use MotherObjectFactory\Feature;
use MotherObjectFactory\Specification;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

class Php8NetteFactory implements Factory
{

    public function __construct(
        private readonly string $methodNameInChildClassUsedToConstructIt = '__construct',
        private string $staticFactoryMethodNameFroDefaultChildObject = 'any',
        private string $staticFactoryMethodForDefaultBuilder = 'newObject',
        private string $buildMethodName = 'build',
        private string $setterPrefix = 'with',
        private bool $privateConstructor = true
    ) {
    }

    public static function createDefault(): self
    {
        return new self();
    }

    /**
     * @throws ReflectionException
     */
    public function create(Specification $specification): string
    {
        $file = new PhpFile();
        $file->setStrictTypes();
        $namespace = $file->addNamespace($specification->namespace());
        $child = new ReflectionClass($specification->forClass());
        $mother = $namespace->addClass($specification->shortClassName());
        $mother->setFinal();
        $this->apply($mother, $child);
        return (string)$file;
    }

    public function canCreateFrom(Specification $specification): bool
    {
        return true;
    }

    private function apply(ClassType $mother, ReflectionClass $child): void
    {
        $this->addConstructor($mother, $child);
        $this->addProperties($mother, $child);
        $this->addSetters($mother, $child);
        $this->addStaticFactoryMethodCreatingDefaultBuilder($mother, $child);
        $this->addStaticFactoryMethodCreatingDefaultChildObject($mother, $child);
        $this->addBuildMethod($mother, $child);
    }

    private function addConstructor(ClassType $mother, ReflectionClass $child): void
    {
        $construct = $mother->addMethod(self::CONSTRUCTOR);

        $childConstructorParameters = $child->getConstructor()->getParameters();
        foreach ($this->parametersUsedToConstructChildObject() as $constructorParameter) {
            $construct->addBody("\$this->{$constructorParameter->getName()}=\${$constructorParameter->getName()};");
            $parameter = $construct->addParameter($constructorParameter->getName());
            $parameter->setNullable($constructorParameter->allowsNull());
            $type = $constructorParameter->hasType() ? (string)$constructorParameter->getType() : null;
            if ($constructorParameter->getType()?->isBuiltin()) {
            }
            $parameter->setType($type);
        }
    }

    protected function addSetters(ClassType $mother, ReflectionClass $child): void
    {
        foreach ($child->getConstructor()->getParameters() as $parameter) {
            $setter = $mother->addMethod($this->setterPrefix . ucfirst($parameter->getName()));
            $setter->addParameter($parameter->getName())
                ->setType($parameter->hasType() ? (string)$parameter->getType() : null);
            $setter->setReturnType('self');
            $setter->setBody("\$this->{$parameter->getName()}=\${$parameter->getName()}; return \$this;");
        }
    }

    private function addBuildMethod(ClassType $mother, ReflectionClass $child): void
    {
        $create = $mother->addMethod($this->buildMethodName);
        $create->setReturnType('\\' . $child->getName());
        $propertyCalls = array_map(
            fn(ReflectionParameter $parameter) => "\$this->{$parameter->getName()}",
            $child->getConstructor()->getParameters()
        );
        $create->setBody(
            sprintf(
                "return new \%s(%s);",
                $child->getName(),
                implode(',', $propertyCalls)
            )
        );
    }

    private function addStaticFactoryMethodCreatingDefaultChildObject(ClassType $mother, ReflectionClass $child): void
    {
        $any = $mother->addMethod($this->staticFactoryMethodNameFroDefaultChildObject);
        $any->setReturnType('\\' . $child->getName());
        $any->setStatic();
        $any->addBody(
            sprintf('return self::%s()->%s();', $this->staticFactoryMethodForDefaultBuilder, $this->buildMethodName)
        );
    }

    private function addStaticFactoryMethodCreatingDefaultBuilder(ClassType $mother, ReflectionClass $child): void
    {
        $newObject = $mother->addMethod($this->staticFactoryMethodForDefaultBuilder);
        $newObject->setReturnType('self');
        $newObject->setStatic();
        $newObject->addBody("return new self(");
        $values = [];
        $faker = \Faker\Factory::create();
        foreach ($child->getConstructor()->getParameters() as $parameter) {
            if ($parameter->isDefaultValueAvailable()) {
                $default = $parameter->getDefaultValue();
                if (is_string($default)) {
                    $default = '"' . $default . '"';
                }

                $values[] = var_export($default, true);
            } else {
                if (method_exists($faker, $parameter->name)) {
                    $values[] = $faker->{$parameter->name}();
                } else {
                    if (!$parameter->hasType()) {
                        $values[] = null;
                        continue;
                    }

                    switch ($parameter->getType()) {
                        case 'string':
                            $values[] = '"' . $faker->text() . '"';
                            break;
                        case 'int':
                            $values[] = $faker->randomNumber();
                            break;
                        case 'float':
                            $values[] = $faker->randomFloat();
                        case 'bool':
                            $values[] = true;
                        case 'mixed':
                            $values[] = null;
                        case 'array':
                            $values[] = '[]';
                    }
                }
            }
        }

        $newObject->addBody(implode(',', $values));
        $newObject->addBody(");");
    }

    private function addProperties(ClassType $mother, ReflectionClass $child): void
    {
        foreach ($child->getConstructor()->getParameters() as $parameter) {
            $mother->addProperty($parameter->name)
                ->setType($parameter->hasType() ? (string)$parameter->getType() : null);
        }
    }

    public function applyingFeatures(): array
    {
        return [Feature::BUILDER_PATTERN, Feature::STATIC_FACTORY_METHOD_PATTERN];
    }

//    /**
//     * @return \ReflectionParameter[]
//     */
//    private function parametersUsedToConstructChildObject(\ReflectionClass $child): array
//    {
//        if ($this->methodNameInChildClassUsedToConstructIt === self::CONSTRUCTOR) {
//            if ($child->getConstructor()->isPrivate() || $child->getConstructor()->isProtected()) {
//                throw new \RuntimeException('Cannot use private method %s for constructing child object');
//            }
//
//            return $child->getConstructor()->getParameters();
//        }
//
//        if ($this -)
//    }

    private const CONSTRUCTOR = '__construct';
}