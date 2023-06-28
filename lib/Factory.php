<?php

declare(strict_types=1);

namespace MotherObjectFactory;

interface Factory
{
    /**
     * Takes mother object specification and generates file content with code (class).
     */
    public function create(Specification $specification): string;

    public function canCreateFrom(Specification $specification): bool;
}