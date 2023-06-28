<?php

declare(strict_types=1);

namespace MotherObjectFactory;

class Specification
{
    private function __construct(
        private readonly string $className,
        private readonly string $namespace,
        private readonly string $forClass,
        private readonly Features $features = new Features([])
    ) {
    }

    /**
     * @throws \ReflectionException when invalid class name given.
     * It would be most likely case where class is not loaded or not full class name was given.
     */
    public static function createForClass(
        string $class,
        string $namespace
    ): self {
        $className = sprintf('%sMother', (new \ReflectionClass($class))->getShortName());
        return new self(
            className: $className,
            namespace: $namespace,
            forClass: $class
        );
    }

    public function addFeature(Feature $feature): self
    {
        $this->features->add($feature);
        return $this;
    }

    public function shortClassName(): string
    {
        return $this->className;
    }

    public function namespace(): string
    {
        return $this->namespace;
    }

    public function forClass(): string
    {
        return $this->forClass;
    }

    public function fullClassName(): string
    {
        return sprintf('%s\\%s', $this->namespace(), $this->shortClassName());
    }

    public function features(): Features
    {
        return $this->features;
    }
//    public function storeOnFileSystem(string $location): void
//    {
//        if (!is_dir(dirname($location))) {
//            @mkdir(directory: dirname($location), recursive: true) or throw new \RuntimeException(
//                sprintf(
//                    "Couldn't create directory: %s Due to: %s",
//                    dirname($location),
//                    error_get_last()['message']
//                )
//            );
//        }
//        $file = new PhpFile();
//        $file->addNamespace($this->namespace);
//        $file->setStrictTypes();
//        $fileContent = <<<PHP
//<?php
//declare(strict_types=1);
//
//namespace {$this->namespace};
//
//PHP;
//        $fileContent .= $this->content;
//        @file_put_contents($location, $fileContent) or throw new \RuntimeException(
//            "Couldn't write to file. %s",
//            error_get_last()['message']
//        );
//    }
}