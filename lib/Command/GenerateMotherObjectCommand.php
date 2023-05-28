<?php
declare(strict_types=1);

namespace MotherOfAllObjects\Command;

use MotherOfAllObjects\MotherObjectFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class GenerateMotherObjectCommand extends Command
{
    public function __construct()
    {
        parent::__construct('generate');
        $this->addArgument(
            'class',
            InputArgument::REQUIRED,
            'Namespace of class for which we want to generate mother-object'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $composerJsonPath = PROJECT_ROOT_DIR . '/composer.json';
        $composerJson = json_decode(file_get_contents($composerJsonPath), true);
        if (!$composerJson) {
            $output->writeln('Couldn\'t find and load composer.json file under path: ' . $composerJsonPath);
            return Command::FAILURE;
        }
        $class = new \ReflectionClass($input->getArgument('class'));
        $declaredNamespaces = [
            ...$composerJson['autoload']['psr-0'] ?? [],
            ...$composerJson['autoload']['psr-4'] ?? [],
            ...$composerJson['autoload-dev']['psr-0'] ?? [],
            ...$composerJson['autoload-dev']['psr-4'] ?? []
        ];
        $namespaceQuestion = new Question(
            'In which namespace, mother object shall be created?'
        );
        $namespaceQuestion->setAutocompleterValues(array_keys($declaredNamespaces));
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $chosenNamespace = $helper->ask($input, $output, $namespaceQuestion);
        $motherObjectLocation = null;
        foreach ($declaredNamespaces as $namespace => $directory) {
            if (!str_starts_with($chosenNamespace, (string)$namespace)) {
                continue;
            }

            $addedNamespace = str_replace((string)$namespace, '', $chosenNamespace);
            if ($addedNamespace) {
                $directory = str_replace('\\', '/', $addedNamespace) . '/';
            }
            $file = $directory . $class->getShortName() . 'MotherObject.php';
            mkdir($directory, recursive: true);
            file_put_contents($file, MotherObjectFactory::create($input->getArgument('class')));
        }

        return Command::SUCCESS;
    }

}