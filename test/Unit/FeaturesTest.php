<?php

declare(strict_types=1);

namespace Tests\MotherObjectFactory\Unit;

use MotherObjectFactory\Feature;
use MotherObjectFactory\Features;
use PHPUnit\Framework\TestCase;

class FeaturesTest extends TestCase
{
    public function testAdding_NewFeature_FeatureAdded(): void
    {
        $features = new Features([]);
        $feature = Feature::BUILDER_PATTERN;

        $features->add($feature);

        $this->assertTrue($features->has($feature));
        $this->assertCount(1, $features);
    }

    public function testAdding_AlreadyAddedFeature_FeatureIsNotAdded(): void
    {

        $feature = Feature::BUILDER_PATTERN;
        $features = new Features([$feature]);

        $features->add($feature);

        $this->assertTrue($features->has($feature));
        $this->assertCount(1, $features);
    }

    public function testHas_HasNotFeature_ReturnsFalse(): void
    {
        $feature = Feature::BUILDER_PATTERN;
        $features = new Features([]);

        $this->assertFalse($features->has($feature));
    }

    public function testEmpty_HasNoFeatures_ReturnTrue(): void
    {
        $features = new Features([]);

        $this->assertTrue($features->empty());
    }

    public function testEmpty_HasFeatures_ReturnFalse(): void
    {
        $features = new Features([Feature::BUILDER_PATTERN]);

        $this->assertFalse($features->empty());
    }

    public function testDiff_HasSameValues_ReturnsEmptyCollection(): void
    {
        $features = new Features([Feature::BUILDER_PATTERN]);
        $otherFeatures = new Features([Feature::BUILDER_PATTERN]);

        $diff = $features->diff($otherFeatures);

        $this->assertTrue($diff->empty());
    }

    public function testDiff_HasDifferentValues_ReturnsCollectionOfFeaturesNotPresentInOtherCollection(): void
    {
        $features = new Features([Feature::BUILDER_PATTERN, Feature::STATIC_FACTORY_METHOD_PATTERN]);
        $otherFeatures = new Features([Feature::BUILDER_PATTERN]);

        $diff = $features->diff($otherFeatures);

        $this->assertCount(1, $diff);
        $this->assertTrue($diff->has(Feature::STATIC_FACTORY_METHOD_PATTERN));
    }

    public function testAsStrings(): void
    {
        $features = new Features([Feature::BUILDER_PATTERN, Feature::STATIC_FACTORY_METHOD_PATTERN]);
        $expectedArray = [Feature::BUILDER_PATTERN->value, Feature::STATIC_FACTORY_METHOD_PATTERN->value];

        $asStrings = $features->asStrings();

        $this->assertSame($expectedArray, $asStrings);
    }
}