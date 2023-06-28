<?php

declare(strict_types=1);

namespace MotherObjectFactory;

use Traversable;

/**
 * @implements \IteratorAggregate<int, Feature>
 */
class Features implements \IteratorAggregate, \Countable
{
    /** @param Feature[] $features */
    public function __construct(private array $features)
    {
    }

    public function add(Feature $feature): void
    {
        if (!$this->has($feature)) {
            $this->features[] = $feature;
        }
    }

    public function has(Feature $feature): bool
    {
        return in_array($feature, $this->features, true);
    }

    /**
     * @return string[]
     */
    public function asStrings(): array
    {
        return array_map(fn(Feature $feature) => $feature->value, $this->features);
    }

    public function diff(Features $features): Features
    {
        $onlyMyFeatures = [];
        foreach ($this->features as $myFeature) {
            $hasMyFeature = false;
            foreach ($features->features as $otherFeature) {
                if ($otherFeature === $myFeature) {
                    $hasMyFeature = true;
                    break;
                }
            }
            if (!$hasMyFeature) {
                $onlyMyFeatures[] = $myFeature;
            }
        }

        return new self($onlyMyFeatures);
    }

    public function empty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * @return Traversable<int, Feature>
     */
    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->features);
    }

    public function count(): int
    {
        return \count($this->features);
    }
}