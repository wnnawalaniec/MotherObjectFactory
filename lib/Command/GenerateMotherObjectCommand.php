<?php

declare(strict_types=1);

namespace MotherObjectFactory\Command;

use MotherObjectFactory\Factory\Php8NetteFactory;
use MotherObjectFactory\Specification;
use MotherObjectFactory\SpecificationBuilder;
use MotherObjectFactory\Tools\NamespaceUtils;
use Mouf\Composer\ClassNameMapper;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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
        $this->addArgument(
            self::MOTHER_OBJECT_NAMESPACE,
            InputArgument::OPTIONAL,
            "Namespace in which mother object should be added (remember to use double \\\\ to separate namespace parts)."
        );
        $this->addArgument(
            self::OUTPUT_FILE,
            InputArgument::OPTIONAL,
            "Output file location with mother object class."
        );
        $this->rootPath = realpath($rootPath);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $setup = SpecificationBuilder::create()
                ->applyBuilderPattern()
                ->applyStaticFactoryMethod();
            // step 1 - determine class
            if (!$input->getArgument(self::CLASS_ARGUMENT)) {
                $setup->createForClass($this->askForClass($input, $output));
            } else {
                $setup->createForClass($input->getArgument(self::CLASS_ARGUMENT));
            }

            // step 2 - determine in which namespace we want to have mother object being generated
            if (!$input->getArgument(self::MOTHER_OBJECT_NAMESPACE)) {
                $motherObjectNamespace = $this->askForMotherObjectNamespace($input, $output);
            } else {
                $motherObjectNamespace = $input->getArgument(self::MOTHER_OBJECT_NAMESPACE);
            }

            if (str_ends_with($motherObjectNamespace, '\\')) {
                $motherObjectNamespace = substr($motherObjectNamespace, 0, -1);
            }

            // create mother object representation
            $motherObject = $setup->createInNamespace($motherObjectNamespace)->build();
            if (!$input->getArgument(self::OUTPUT_FILE)) {
                $outputFile = $this->askForOutputFile($input, $output, $motherObject);
            } else {
                $outputFile = $input->getArgument(self::OUTPUT_FILE);
            }

            $factory = Php8NetteFactory::createDefault();
            @mkdir(dirname($outputFile));
            file_put_contents($outputFile, $factory->create($motherObject));
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
            return array_values(NamespaceUtils::findMatchingElements($input, $classes));
        };
    }

    /**
     * @throws \Exception
     */
    private function namespacesAutocomplete(): \Closure
    {
        $classes = $this->listProjectClasses();
        return function (string $input) use ($classes): array {
            return array_values(
                array_filter(
                    NamespaceUtils::findMatchingElements($input, $classes),
                    fn($str) => str_ends_with($str, '\\')
                )
            );
        };
    }

    private function askForOutputFile(InputInterface $input, OutputInterface $output, Specification $motherObject)
    {
        $possibleLocations = $this->possibleFileLocationForNamespace($motherObject);
        $default = $possibleLocations[0] ?? null;
        $questionText = 'Mother object class file?';
        if ($default) {
            $questionText .= ' [' . $default . ']';
        }

        $question = new Question($questionText, $default);
        $question->setAutocompleterValues($possibleLocations);
        return $this->getHelper('question')->ask($input, $output, $question);
    }

    protected function possibleFileLocationForNamespace(Specification $motherObject): array
    {
        return ClassNameMapper::createFromComposerFile(
            rootPath: $this->rootPath,
            useAutoloadDev: true
        )->getPossibleFileNames(
            $motherObject->fullClassName()
        );
    }

    private const CONSTRUCTING_METHOD = 'method';
    private const CLASS_ARGUMENT = 'class';
    private const MOTHER_OBJECT_NAMESPACE = 'namespace';
    private const OUTPUT_FILE = 'output';
    private string $rootPath;
}