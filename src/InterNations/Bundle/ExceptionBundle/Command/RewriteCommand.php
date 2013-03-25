<?php
namespace InterNations\Bundle\ExceptionBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use InterNations\Bundle\ExceptionBundle\Rewriter\ExceptionRewriter;
use Symfony\Component\Finder\Finder;
use SplFileObject;
use Functional as F;

class RewriteCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('exception:rewrite')
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
                ]
            )
            ->addOption('lint', null, InputOption::VALUE_REQUIRED, 'Lint PHP files', true)
            ->setDescription('Rewrites global exception classes to bundle specific exception classes');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $namespace = trim($input->getArgument('namespace'), '\\');
        $target = trim($input->getArgument('target'), '/\\');

        $rewriter = new ExceptionRewriter($namespace);

        $files = Finder::create()
            ->ignoreVCS(true)
            ->in($target . DIRECTORY_SEPARATOR . 'Exception')
            ->name('*Exception.php');
        foreach ($files as $exceptionFile) {
            $exceptionClassName = str_replace('.php', '', $exceptionFile->getFileName());
            $fqExceptionClassName = $namespace . '\\' . $exceptionClassName;
            $rewriter->registerBundleException($fqExceptionClassName);
            $output->writeln(
                sprintf(
                    '<info>Found bundle specific exception class %s</info>',
                    $exceptionClassName
                )
            );
        }

        $changeReports = [];
        $filesAnalyzed = 0;
        foreach (Finder::create()->ignoreVCS(true)->in($target)->name('*.php') as $file) {
            $filesAnalyzed++;
            $report = $rewriter->rewrite(new SplFileObject($file->getPathName(), 'r+'));
            if ($report->fileChanged()) {
                $changeReports[] = $report;
            }
            if ($filesAnalyzed % 60 === 0) {
                $output->writeln('.');
            } else {
                $output->write('.');
            }
        }


        $line = str_repeat('-', 60);
        $output->writeln('');
        $output->writeln('');
        $output->writeln('<info>' . $line . '</info>');
        $output->writeln('<info>' . $line . '</info>');
        $output->writeln('<info>SUMMARY</info>');
        $output->writeln('<info>' . $line . '</info>');
        $output->writeln('<info>' . $line . '</info>');
        $output->writeln(sprintf('Files analyzed:               %d', $filesAnalyzed));
        $output->writeln(sprintf('Files changed:                %d', count($changeReports)));
        $output->writeln($line);
        $output->writeln(
            sprintf('"throw" statements found:     %d', F\sum(F\pluck($changeReports, 'throwStatementsFound')))
        );
        $output->writeln(
            sprintf('"throw" statements rewritten: %d', F\sum(F\pluck($changeReports, 'throwStatementsRewritten')))
        );
        $output->writeln($line);
        $output->writeln(
            sprintf('"use" statements found:       %d', F\sum(F\pluck($changeReports, 'useStatementsFound')))
        );
        $output->writeln(
            sprintf('"use" statements rewritten:   %d', F\sum(F\pluck($changeReports, 'useStatementsRewritten')))
        );
        $output->writeln(
            sprintf('"use" statements added:       %d', F\sum(F\pluck($changeReports, 'useStatementsAdded')))
        );
        $output->writeln($line);
        $output->writeln(
            sprintf('"catch" statements found:     %d', F\sum(F\pluck($changeReports, 'catchStatementsFound')))
        );
    }
}
