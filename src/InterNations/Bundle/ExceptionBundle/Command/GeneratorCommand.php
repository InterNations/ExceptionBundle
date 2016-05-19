<?php
namespace InterNations\Bundle\ExceptionBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use InterNations\Bundle\ExceptionBundle\CodeGenerator\ExceptionGenerator;
use InterNations\Bundle\ExceptionBundle\CodeGenerator\MarkerInterfaceGenerator;
use ReflectionClass;

class GeneratorCommand extends ContainerAwareCommand
{
    protected function configure()
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

    public function execute(InputInterface $input, OutputInterface $output)
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

    protected function getSplExceptionClasses()
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

    protected function getClassFile($target, $className)
    {
        return sprintf('%s%s%s.php', $target, DIRECTORY_SEPARATOR, $className);
    }

    protected function getHierarchy($name)
    {
        $hierarchy = [];

        $previousClassName = null;

        foreach (explode(':', $name) as $className) {
            $hierarchy[$previousClassName] = $className;
            $previousClassName = $className;
        }

        return $hierarchy;
    }

    private function writeFile(InputInterface $input, OutputInterface $output, $fileName, $content)
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
