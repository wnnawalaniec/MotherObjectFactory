<?php
declare(strict_types=1);

namespace MotherObjectFactory;

use Nette\PhpGenerator\ClassType;

interface Enricher
{
    public function enrich(ClassType $mother, \ReflectionClass $child): void;
}