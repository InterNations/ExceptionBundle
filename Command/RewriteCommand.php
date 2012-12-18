<?php
namespace InterNations\Bundle\ExceptionBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use InterNations\Bundle\ExceptionBundle\Rewriter\ExceptionRewriter;
use Symfony\Component\Finder\Finder;

class RewriteCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('exception:rewrite')
            ->setDefinition(
                array(
                    new InputArgument('target', InputArgument::REQUIRED, 'The target directory. E.g. "app/src/Foo/Bundle/TestBundle"'),
                    new InputArgument('namespace', InputArgument::REQUIRED, 'The target namespace. E.g. "Foo\Bundle\TestBundle"'),
                )
            )
            ->addOption('lint', null, InputOption::VALUE_REQUIRED, 'Lint PHP files', true)
            ->setDescription('Rewrites global exception classes to bundle specific exception classes');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $namespace = trim($input->getArgument('namespace'), '\\');
        $target = trim($input->getArgument('target'), '/\\');

        $rewriter = new ExceptionRewriter($namespace);

        foreach (Finder::create()->ignoreVCS(true)->in($target . DIRECTORY_SEPARATOR . 'Exception')->name('*Exception.php') as $exceptionFile) {
            $rewriter->registerBundleException($namespace . '\\' . str_replace('.php', '', $exceptionFile->getFileName()));
        }

        foreach (Finder::create()->ignoreVCS(true)->in($target)->name('*.php') as $file) {
            $file = new \SplFileObject($file->getPathName(), 'r+');
            $rewriter->rewrite($file);
        }
    }
}