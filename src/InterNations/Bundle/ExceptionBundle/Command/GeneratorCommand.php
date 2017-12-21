<?php
namespace InterNations\Bundle\ExceptionBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use InterNations\Bundle\ExceptionBundle\CodeGenerator\ExceptionGenerator;
use InterNations\Bundle\ExceptionBundle\CodeGenerator\MarkerInterfaceGenerator;
use ReflectionClass;

class GeneratorCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('exception:generate')
            ->setDefinition(
                [
                    new InputArgument(
                        'target',
                        InputArgument::REQUIRED,
                        'The target directory. E.g. "app/src/Foo/Bundle/TestBundle"'
                    ),
                    new InputArgument(
                        'namespace',
                        InputArgument::REQUIRED,
                        'The target namespace. E.g. "Foo\Bundle\TestBundle"'
                    ),
                    new InputArgument(
                        'marker-interface',
                        InputArgument::REQUIRED,
                        'Name of the marker interface all exception classes implement. E.g. "ExceptionInterface"'
                    ),
                    new InputArgument(
                        'exceptions',
                        InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                        'Exception classes to be generated ("spl" is a shortcut for all spl exceptions)',
                        ['spl']
                    ),
                ]
            )
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run exception generation')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force overriding files')
            ->addOption('lint', null, InputOption::VALUE_REQUIRED, 'Lint PHP files', true)
            ->setDescription('Generates bundle specific exception base classes');
    }

    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $namespace = trim($input->getArgument('namespace'), '\\');

        if (strpos($namespace, '\\Exception') !== strlen($namespace) - strlen('\\Exception')) {
            $namespace .= '\\Exception';
        }

        $target = trim($input->getArgument('target'), '/\\');

        if (strpos($target, 'Exception') != strlen($namespace) - strlen('Exception')) {
            $target .= DIRECTORY_SEPARATOR . 'Exception';
        }

        $dryRun = $input->getOption('dry-run');

        $exceptionClasses = $input->getArgument('exceptions');

        if (($pos = array_search('spl', $exceptionClasses)) !== false) {
            unset($exceptionClasses[$pos]);
            $exceptionClasses = array_merge($exceptionClasses, $this->getSplExceptionClasses());
        }

        if (!file_exists($target)) {
            if (!is_writable(dirname($target))) {
                $output->writeln('<error>Could not create directory ' . $target . '</error>');

                return;
            }

            $output->writeln('<info>Create directory ' . $target . '</info>');

            if (!$dryRun) {
                mkdir($target);
            }

            if (!$dryRun && !(file_exists($target) && is_dir($target))) {
                $output->writeln('<error>Could not create directory ' . $target . '</error>');

                return;
            }
        }

        $markerInterface = $input->getArgument('marker-interface');

        if ($markerInterface) {

            $markerInterfaceFile = $this->getClassfile($target, $markerInterface);

            $markerInterfaceGenerator = new MarkerInterfaceGenerator($namespace);
            $code = $markerInterfaceGenerator->generate($markerInterface);

            $this->writeFile($input, $output, $markerInterfaceFile, $code);
        }

        $exceptionGenerator = new ExceptionGenerator(
            $namespace,
            $markerInterface ? $namespace . '\\' . $markerInterface : ''
        );

        foreach ($exceptionClasses as $hierarchy) {
            foreach ($this->getHierarchy($hierarchy) as $parentExceptionClass => $exceptionClass) {
                $exceptionFile = $this->getClassFile($target, $exceptionClass);
                $code = $exceptionGenerator->generate($exceptionClass, $parentExceptionClass);
                $this->writeFile($input, $output, $exceptionFile, $code);
            }
        }
    }

    /** @return string[] */
    private function getSplExceptionClasses(): array
    {
        $exceptionClasses = [];

        foreach (spl_classes() as $class) {
            $reflected = new ReflectionClass($class);

            if ($reflected->isSubclassOf('Exception')) {
                $exceptionClasses[] = $class;
            }
        }

        return $exceptionClasses;
    }

    private function getClassFile(string $target, string $className): string
    {
        return sprintf('%s%s%s.php', $target, DIRECTORY_SEPARATOR, $className);
    }

    /** @return string[] */
    private function getHierarchy(string $name): array
    {
        $hierarchy = [];

        $previousClassName = null;

        foreach (explode(':', $name) as $className) {
            $hierarchy[$previousClassName] = $className;
            $previousClassName = $className;
        }

        return $hierarchy;
    }

    private function writeFile(InputInterface $input, OutputInterface $output, string $fileName, string $content): void
    {
        if (!$input->getOption('force') && file_exists($fileName)) {
            $output->writeln(sprintf('<error>Skipping file %s</error>', $fileName));

            return;
        }

        if ($input->getOption('dry-run')) {
            $output->writeln(sprintf('<info>Writing %s (dry run)</info>', $fileName));

            return;
        }

        $output->writeln(sprintf('<info>Writing %s</info>', $fileName));
        file_put_contents($fileName, $content);

        if (!file_exists($fileName)) {
            $output->writeln(sprintf('<error>Could not write file %s</error>', $fileName));

            return;
        }

        if (!$input->getOption('lint')) {
            return;
        }

        exec('php -l ' . escapeshellarg($fileName), $stdOut, $returnValue);

        if ($returnValue != 0) {
            $output->writeln(sprintf('<error>File %s contains syntax errors</error>', $fileName));
        }
    }
}
