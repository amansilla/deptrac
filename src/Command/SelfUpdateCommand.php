<?php

namespace SensioLabs\Deptrac\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class SelfUpdateCommand extends Command
{
    public function configure()
    {
        $this
            ->setName('self-update')
            ->setAliases(array('selfupdate'))
            ->setDescription('Updates deptrac.phar to the latest version.')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $latest = trim(file_get_contents('http://get.sensiolabs.de/deptrac.version'));
        if (empty($latest)) {
            $output->writeln('<error>Latest deptrac version info could not be fetched.</error>');

            return 1;
        }

        if ($this->getApplication()->getVersion() === $latest) {
            $output->writeln(sprintf('<info>You are already using the latest deptrac version %s</info>', $latest));

            return 0;
        } else {
            $output->writeln(sprintf('<info>Updating deptrac to version %s</info>.', $latest));
        }

        $localFilename = realpath($_SERVER['argv'][0]) ?: $_SERVER['argv'][0];
        $tmpDir = is_writable(dirname($localFilename)) ? dirname($localFilename) : false;
        if (false === $tmpDir) {
            $output->writeln(sprintf('<error>Deptrac self-update failed: %s not writable.</error>', $tmpDir));

            return 1;
        }

        $tmpFilename = $tmpDir . '/deptrac-tmp.phar';
        file_put_contents($tmpFilename, file_get_contents('http://get.sensiolabs.de/deptrac.phar'));
        if (false === file_exists($tmpFilename)) {
            $output->writeln(sprintf('<error>Deptrac couldn\'t be downloaded.</error>', $tmpDir));

            return 1;
        }

        rename($tmpFilename, $localFilename);
        $output->writeln('<info>Deprac was successfully updated.</info>');
    }
}
