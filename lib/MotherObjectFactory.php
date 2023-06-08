<?php
declare(strict_types=1);

namespace MotherObjectFactory;

use MotherObjectFactory\Enrichers\Builder;
use MotherObjectFactory\Enrichers\AnyMethod;
use Nette\PhpGenerator\ClassType;

final class MotherObjectFactory
{
    public function __construct(array $enrichers)
    {
        $this->enrichers = $enrichers;
    }

    /**
     * @throws \ReflectionException
     */
    public function create(string $class): string
    {
        $childReflection = new \ReflectionClass($class);
        $motherClass = new ClassType("{$childReflection->getShortName()}Mother");
        $motherClass->setFinal();
        foreach ($this->enrichers as $enricher) {
            $enricher->enrich($motherClass, $childReflection);
        }

        return (string)$motherClass;
    }

    public static function instance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = self::default();
        }

        return self::$instance;
    }

    private static function default(): self
    {
        return new self([
            new Builder(),
            new AnyMethod()
        ]);
    }

    private static ?self $instance = null;
    /** @var Enricher[] */
    private array $enrichers = [];
}