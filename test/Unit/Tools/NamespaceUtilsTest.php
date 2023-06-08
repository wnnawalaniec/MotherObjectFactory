<?php

declare(strict_types=1);

namespace Tests\MotherObjectFactory\Unit\Tools;

use MotherObjectFactory\Tools\NamespaceUtils;
use PHPUnit\Framework\TestCase;

class NamespaceUtilsTest extends TestCase
{
    public function testAggregatingClassesByNamespace(): void
    {
        $classes = [
            'A\B\Foo',
            'A\B\Bar',
            'A\Foo',
            'Foo'
        ];
        $expectedResult = [
            'A' => [
                'B' => [
                    'Foo',
                    'Bar'
                ],
                'Foo'
            ],
            'Foo'
        ];

        $result = NamespaceUtils::aggregateClassesByNamespaceElements($classes);

        $this->assertEquals($expectedResult, $result);
    }

    public function testAggregatingClassesByNamespaceWithEmptyInput(): void
    {
        $classes = [];
        $expectedResult = [];

        $result = NamespaceUtils::aggregateClassesByNamespaceElements($classes);

        $this->assertEquals($expectedResult, $result);
    }

    public function testAggregatingClassesByNamespaceWithDifferentNamespaceDepths(): void
    {
        $classes = [
            'A\B\C\Class1',
            'A\B\Class2',
            'A\Class3',
            'Class4'
        ];
        $expectedResult = [
            'A' => [
                'B' => [
                    'C' => [
                        'Class1'
                    ],
                    'Class2'
                ],
                'Class3'
            ],
            'Class4'
        ];

        $result = NamespaceUtils::aggregateClassesByNamespaceElements($classes);

        $this->assertEquals($expectedResult, $result);
    }

    public function testAggregatingClassesByNamespaceWithSameNamespaceElements(): void
    {
        $classes = [
            'A\B\Foo',
            'A\B\Bar',
            'A\B\Baz'
        ];
        $expectedResult = [
            'A' => [
                'B' => [
                    'Foo',
                    'Bar',
                    'Baz'
                ]
            ]
        ];

        $result = NamespaceUtils::aggregateClassesByNamespaceElements($classes);

        $this->assertEquals($expectedResult, $result);
    }

    public function testGettingAllSubNamespacesWhenIncompleteNamespaceGiven(): void
    {
        $classes = [
            'Top\\SubA\\Foo',
            'Top\\SubA\\Fix',
            'Top\\SubB\\Baz',
            'Top\\SubC',
            'Top\\Bar'
        ];

        $namespace = 'Top\\Sub';
        $expectedSubNamespaces = [
            'Top\\SubA\\',
            'Top\\SubB\\',
            'Top\\SubC'
        ];
        $this->assertEqualsCanonicalizing($expectedSubNamespaces, NamespaceUtils::allSubNamespaces($namespace, $classes));


        $namespace = 'Top\\SubA';
        $expectedSubNamespaces = [
            'Top\\SubA\\'
        ];
        $this->assertEqualsCanonicalizing($expectedSubNamespaces, NamespaceUtils::allSubNamespaces($namespace, $classes));
    }

    public function testGettingAllSubNamespacesWhenCompleteNamespaceGiven(): void
    {
        $classes = [
            'Top\\SubA\\Foo',
            'Top\\SubA\\Baz',
            'Top\\SubC',
            'Top\\Bar'
        ];
        $namespace = 'Top\\SubA\\';
        $expectedSubNamespaces = [
            'Top\\SubA\\Foo',
            'Top\\SubA\\Baz',
        ];

        $result = NamespaceUtils::allSubNamespaces($namespace, $classes);

        $this->assertEqualsCanonicalizing($expectedSubNamespaces, $result);
    }

    public function testGettingAllSubNamespacesWhenNoMatch(): void
    {
        $classes = [
            'Top\\SubA\\Foo',
            'Top\\SubA\\Baz',
            'Top\\SubC',
            'Top\\Bar'
        ];
        $namespace = 'XYZ';
        $expectedSubNamespaces = [
        ];

        $result = NamespaceUtils::allSubNamespaces($namespace, $classes);

        $this->assertEqualsCanonicalizing($expectedSubNamespaces, $result);
    }
}
