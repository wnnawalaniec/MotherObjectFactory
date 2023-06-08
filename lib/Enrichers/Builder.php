<?php
declare(strict_types=1);

namespace MotherObjectFactory\Enrichers;

use Faker\Factory;
use MotherObjectFactory\Enricher;
use Nette\PhpGenerator\ClassType;

class Builder extends Properties implements Enricher
{
    public function enrich(ClassType $mother, \ReflectionClass $child): void
    {
        parent::enrich($mother, $child);
        $this->setters($mother, $child);
        $this->builder($mother, $child);
        $this->staticFactory($mother, $child);
    }

    protected function setters(ClassType $mother, \ReflectionClass $child): void
    {
        foreach ($child->getConstructor()->getParameters() as $parameter) {
            $setter = $mother->addMethod('with' . ucfirst($parameter->getName()));
            $setter->addParameter($parameter->getName())
                ->setType($parameter->hasType() ? (string)$parameter->getType() : null);
            $setter->setReturnType('self');
            $setter->setBody("\$this->{$parameter->getName()}=\${$parameter->getName()}; return \$this;");
        }
    }

    private function builder(ClassType $mother, \ReflectionClass $child): void
    {
        $create = $mother->addMethod('build');
        $create->setReturnType('\\' . $child->getName());
        $propertyCalls = array_map(
            fn(\ReflectionParameter $parameter) => "\$this->{$parameter->getName()}",
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

    private function staticFactory(ClassType $mother, \ReflectionClass $child): void
    {
        $newObject = $mother->addMethod('newObject');
        $newObject->setReturnType('self');
        $newObject->setStatic();
        $newObject->addBody("return new self(");
        $values = [];
        $faker = Factory::create();
        foreach ($child->getConstructor()->getParameters() as $parameter) {
            if ($parameter->isDefaultValueAvailable()) {
                $default = $parameter->getDefaultValue();
                if (is_string($default)) {
                    $default = '"' . $default . '"';
                }

                $values[] = $default ?? 'null';
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
                    }
                }
            }
        }

        $newObject->addBody(implode(',', $values));
        $newObject->addBody(");");
    }
}