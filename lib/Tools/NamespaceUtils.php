<?php

declare(strict_types=1);

namespace MotherObjectFactory\Tools;

final class NamespaceUtils
{
    /**
     * Aggregates classes by namespace elements.
     *
     * @param array<string> $classes array like:
     * ```
     * [
     *  'A\B\Foo',
     *  'A\B\Bar',
     *  'A\Foo'
     * ]
     * ```
     * @return array<string> array like:
     * ```
     * [
     *  'A' => [
     *      'B' => [
     *          'Foo',
     *          'Bar'
     *  ],
     *  'Foo'
     * ]
     */
    public static function aggregateClassesByNamespaceElements(array $classes): array
    {
        $aggregatedClasses = [];
        foreach ($classes as $class) {
            $namespaceElements = explode('\\', $class);
            $actualClassName = array_pop($namespaceElements);
            $currentLevel = &$aggregatedClasses;
            foreach ($namespaceElements as $namespaceElement) {
                $currentLevel[$namespaceElement] ??= [];
                $currentLevel = &$currentLevel[$namespaceElement];
            }

            $currentLevel[] = $actualClassName;
        }

        return $aggregatedClasses;
    }

    public static function allSubNamespaces(string $namespace, array $classes): array
    {
        $matching = [];
        foreach ($classes as $class) {
            if (str_starts_with($class, $namespace)) {
                $matching[] = $class;
            }
        }

        $result = [];
        foreach ($matching as $match) {
            $nextSeparator = strpos($match, '\\', strlen($namespace));
            if ($nextSeparator) {
                $result[] = substr($match, 0, $nextSeparator + 1);
            } else {
                $result[] = $match;
            }
        }

        return array_unique($result);
    }
}