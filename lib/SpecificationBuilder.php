<?php

declare(strict_types=1);

namespace MotherObjectFactory;

final class SpecificationBuilder
{
    private function __construct(
        private ?string $forClass = null,
        private ?string $inNamespace = null,
        private bool $staticFactoryMethodPattern = false,
        private bool $builderPattern = false
    ) {
    }

    public static function create(): self
    {
        return new self();
    }

    public function createForClass(string $fullClassName): self
    {
        $this->forClass = $fullClassName;
        return $this;
    }

    public function createInNamespace(string $namespace): self
    {
        $this->inNamespace = $namespace;
        return $this;
    }

    public function applyStaticFactoryMethod(bool $factoryMethodPattern = true): self
    {
        $this->staticFactoryMethodPattern = $factoryMethodPattern;
        return $this;
    }

    public function applyBuilderPattern(bool $builderPattern = true): self
    {
        $this->builderPattern = $builderPattern;
        return $this;
    }

    public function forClass(): string
    {
        return $this->forClass;
    }

    public function inNamespace(): string
    {
        return $this->inNamespace;
    }

    public function staticFactoryMethodPattern(): bool
    {
        return $this->staticFactoryMethodPattern;
    }

    public function builderPattern(): bool
    {
        return $this->builderPattern;
    }

    /**
     * @throws \ReflectionException
     */
    public function build(): Specification
    {
        $motherObject = Specification::createForClass($this->forClass, $this->inNamespace);
        if ($this->builderPattern) {
            $motherObject->addFeature(Feature::BUILDER_PATTERN);
        }
        if ($this->staticFactoryMethodPattern) {
            $motherObject->addFeature(Feature::STATIC_FACTORY_METHOD_PATTERN);
        }
        return $motherObject;
    }
}