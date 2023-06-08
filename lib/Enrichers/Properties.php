<?php
declare(strict_types=1);

namespace MotherObjectFactory\Enrichers;

use MotherObjectFactory\Enricher;
use Nette\PhpGenerator\ClassType;

class Properties implements Enricher
{
    public function enrich(ClassType $mother, \ReflectionClass $child): void
    {
        if (!$child->hasMethod('__construct')) {
            return;
        }
        $this->constructor($mother, $child);
        $this->properties($mother, $child);
    }

    private function constructor(ClassType $mother, \ReflectionClass $child): void
    {
        $construct = $mother->addMethod('__construct');
        $childConstructorParameters = $child->getConstructor()->getParameters();
        foreach ($childConstructorParameters as $constructorParameter) {
            $construct->addBody("\$this->{$constructorParameter->getName()}=\${$constructorParameter->getName()};");
            $parameter = $construct->addParameter($constructorParameter->getName());
            $parameter->setNullable($constructorParameter->allowsNull());
            $parameter->setType($constructorParameter->hasType() ? (string)$constructorParameter->getType() : null);
        }
    }

    private function properties(ClassType $mother, \ReflectionClass $child): void
    {
        foreach ($child->getConstructor()->getParameters() as $parameter) {
            $mother->addProperty($parameter->name)
                ->setType($parameter->hasType() ? (string)$parameter->getType() : null);
        }
    }
}