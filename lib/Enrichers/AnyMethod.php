<?php
declare(strict_types=1);

namespace MotherObjectFactory\Enrichers;

use MotherObjectFactory\Enricher;
use Nette\PhpGenerator\ClassType;

class AnyMethod implements Enricher
{
    public function enrich(ClassType $mother, \ReflectionClass $child): void
    {
        $any = $mother->addMethod('any');
        $any->setReturnType('\\' . $child->getName());
        $any->setStatic();
        $any->addBody(sprintf('return self::%s()->%s();', 'newObject', 'build'));
    }
}