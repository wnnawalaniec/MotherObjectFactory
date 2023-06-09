<?php

declare(strict_types=1);

namespace MotherObjectFactory\Command;

use MotherObjectFactory\MotherObjectFactory;
use MotherObjectFactory\Tools\NamespaceUtils;
use Mouf\Composer\ClassNameMapper;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use TheCodingMachine\ClassExplorer\Glob\GlobClassExplorer;

class GenerateMotherObjectCommand extends Command
{
    public function __construct(string $rootPath)
    {
        parent::__construct('generate');
        $this->setDescription('Generates mother object for class.');
        $this->addArgument(
            self::CLASS_ARGUMENT,
            InputArgument::OPTIONAL,
            "Full class name (remember to use double \\\\ to separate namespace parts)"
            . " for which we want to generate mother object."
        );
        $this->rootPath = realpath($rootPath);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $composer = $this->loadComposerFile($this->rootPath . '/composer.json');
            // step 1 - determine class
            if (!$input->getArgument(self::CLASS_ARGUMENT)) {
                $class = $this->askForClass($input, $output);
            } else {
                $class = $input->getArgument(self::CLASS_ARGUMENT);
            }


            $motherObjectNamespace = $this->askForMotherObjectNamespace($input, $output);
            if (!str_ends_with($motherObjectNamespace, '\\')) {
                $motherObjectNamespace .= '\\';
            }

            $factory = MotherObjectFactory::instance();
            $mapper = ClassNameMapper::createFromComposerFile(rootPath: $this->rootPath, useAutoloadDev: true);
            $childClass = new \ReflectionClass($class);
            $possibleLocations = $mapper->getPossibleFileNames(
                $motherObjectNamespace . $childClass->getShortName() . 'Mother'
            );
            if (\count($possibleLocations) > 1) {
                $location = $this->askForLocation($input, $output, $possibleLocations);
            } else {
                $location = $possibleLocations[0];
            }

            if (!is_dir(dirname($location))) {
                mkdir(directory: dirname($location), recursive: true);
            }

            $fileContent = <<<PHP
<?php
declare(strict_types=1);

namespace {$motherObjectNamespace};

PHP;
            $fileContent .= $factory->create($class);
            file_put_contents($location, $fileContent);
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            return Command::FAILURE;
        }
    }

    private function askForMotherObjectNamespace(InputInterface $input, OutputInterface $output): mixed
    {
        $question = new Question(
            'In which namespace, mother object shall be created?' . PHP_EOL
        );
        $question->setAutocompleterCallback($this->namespacesAutocomplete());
        return $this->getHelper('question')->ask($input, $output, $question);
    }

    /**
     * @throws \Exception
     */
    private function askForClass(InputInterface $input, OutputInterface $output): string
    {
        $question = new Question('For which class do you want to create mother object?' . PHP_EOL);
        $question->setAutocompleterCallback($this->classesAutocomplete());
        return $this->getHelper('question')->ask($input, $output, $question);
    }

    private function askForLocation(InputInterface $input, OutputInterface $output, array $possibleLocations): string
    {
        $question = new ChoiceQuestion('Select location for mother object class:', $possibleLocations, 0);
        return $this->getHelper('question')->ask($input, $output, $question);
    }

    /**
     * @return string[]
     * @throws \Exception
     */
    private function listProjectClasses(): array
    {
        $composer = $this->loadComposerFile($this->rootPath . '/composer.json');
        $namespaces = array_unique(
            array_keys([
                ...$composer['autoload']['psr-0'] ?? [],
                ...$composer['autoload']['psr-4'] ?? [],
                ...$composer['autoload-dev']['psr-0'] ?? [],
                ...$composer['autoload-dev']['psr-4'] ?? [],
            ])
        );
        $psr16Cache = new Psr16Cache(new ArrayAdapter());
        $rootPath = $this->rootPath;
        $mapper = ClassNameMapper::createFromComposerFile(useAutoloadDev: true);
        return array_merge(
            ...array_map(static fn($namespace) => (new GlobClassExplorer(
                namespace: $namespace,
                cache: $psr16Cache,
                classNameMapper: $mapper,
                rootPath: $rootPath
            ))->getClasses(), $namespaces)
        );
    }

    /**
     * @throws \Exception
     */
    private function loadComposerFile(string $location): array
    {
        $composerJson = json_decode(file_get_contents($location), true);
        if (!$composerJson) {
            throw new \Exception('Couldn\'t find and load composer.json file under path: ' . $location);
        }

        return $composerJson;
    }

    /**
     * @throws \Exception
     */
    protected function classesAutocomplete(): \Closure
    {
        $classes = $this->listProjectClasses();
        return function (string $input) use ($classes): array {
            return NamespaceUtils::findMatchingElements($input, $classes);
        };
    }

    /**
     * @throws \Exception
     */
    private function namespacesAutocomplete(): \Closure
    {
        $classes = $this->listProjectClasses();
        return function (string $input) use ($classes): array {
            return array_filter(
                NamespaceUtils::findMatchingElements($input, $classes),
                fn($str) => str_ends_with($str, '\\')
            );
        };
    }

    private const CLASS_ARGUMENT = 'class';
    private string $rootPath;
}