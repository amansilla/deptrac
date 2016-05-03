<?php

namespace SensioLabs\Deptrac\Command;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use GuzzleHttp\Exception\RequestException;

class SelfUpdateCommand extends Command
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * SelfUpdateCommand constructor.
     *
     * @param Filesystem $filesystem
     * @param Client     $client
     */
    public function __construct(Filesystem $filesystem, Client $client)
    {
        $this->filesystem = $filesystem;
        $this->httpClient = $client;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('self-update')
            ->setAliases(array('selfupdate'))
            ->setDescription('Updates deptrac.phar to the latest version.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Updating deptrac to latest version...</info>');

        $tmpFilename = sys_get_temp_dir() . '/deptrac.phar';

        try {
            $this->httpClient->get('http://get.sensiolabs.de/deptrac.phar', [RequestOptions::SINK => $tmpFilename]);

            $localFilename = realpath($_SERVER['argv'][0]) ?: $_SERVER['argv'][0];
            $localFilePerm = fileperms($localFilename);
            try {
                if (!$this->filesystem->exists($tmpFilename)) {
                    $output->writeln('<error>Could not write temporary file.</error>');

                    return 1;
                }

                $this->filesystem->rename($tmpFilename, $localFilename, true);
                $this->filesystem->chmod($localFilename, $localFilePerm);

            } catch (IOException $e) {
                $output->writeln('<error>Could not write deptrac.phar</error>');

                return 1;
            }

        } catch (RequestException $e) {
            $output->writeln('<error>Could not download http://get.sensiolabs.de/deptrac.phar.</error>');

            return 1;
        }

        $output->writeln('<info>Deprac was successfully updated.</info>');
    }
}
